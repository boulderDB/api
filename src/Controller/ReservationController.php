<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Repository\ReservationRepository;
use App\Repository\RoomRepository;
use App\Repository\TimeSlotRepository;
use App\Controller\FormErrorTrait;
use App\Controller\RequestTrait;
use App\Controller\ResponseTrait;
use App\Entity\User;
use App\Factory\RedisConnectionFactory;
use App\Service\ContextService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * @Route("/reservation")
 */
class ReservationController extends AbstractController
{
    use FormErrorTrait;
    use RequestTrait;
    use ResponseTrait;

    private EntityManagerInterface $entityManager;
    private ContextService $contextService;
    private TimeSlotRepository $timeSlotRepository;
    private ReservationRepository $reservationRepository;
    private RoomRepository $roomRepository;
    private \Redis $redis;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContextService $contextService,
        TimeSlotRepository $timeSlotRepository,
        ReservationRepository $reservationRepository,
        RoomRepository $roomRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->contextService = $contextService;
        $this->timeSlotRepository = $timeSlotRepository;
        $this->reservationRepository = $reservationRepository;
        $this->roomRepository = $roomRepository;

        $this->redis = RedisConnectionFactory::create();
    }

    /**
     * @Route(methods={"post"})
     */
    public function create(Request $request)
    {
        $reservation = new Reservation();
        $reservation->setUser($this->getUser());

        $form = $this->createForm(ReservationType::class, $reservation);
        $form->submit(json_decode($request->getContent(), true));

        if (!$form->isValid()) {
            return $this->badFormRequestResponse($form);
        }

        $reservation->generateHashId();

        if ($this->reservationRepository->findPendingTimeSlotReservationId($reservation->getHashId(), $this->getUser()->getId())) {
            return $this->json([
                "message" => "There already exists a pending reservation for this time slot.",
                "code" => Response::HTTP_CONFLICT
            ], Response::HTTP_CONFLICT);
        }

        // daily limiter?
        if ($this->reservationRepository->hasPendingReservationForDate($reservation->getDate(), $reservation->getUser()->getId())) {

            return $this->json([
                "message" => "There already exists a pending reservation for this day.",
                "code" => Response::HTTP_CONFLICT
            ], Response::HTTP_CONFLICT);
        }

        try {
            $capacity = $this->timeSlotRepository->getCapacity(
                $this->contextService->getLocation()->getId(),
                $reservation->getRoom()->getId(),
                strtolower($reservation->getDate()->format("l")),
                $reservation->getStartTime(),
                $reservation->getEndTime()
            );

        } catch (EntityNotFoundException $e) {

            return $this->json([
                "message" => $e->getMessage(),
                "code" => Response::HTTP_NOT_FOUND

            ], Response::HTTP_NOT_FOUND);
        }

        $blocked = $this->reservationRepository->countHashIds($reservation->getHashId());

        if ($capacity === $blocked) {

            return $this->json([
                "message" => "This time slot is full.",
                "code" => Response::HTTP_CONFLICT
            ], Response::HTTP_CONFLICT);
        }

        $this->entityManager->persist($reservation);
        $this->entityManager->flush();

        return $this->createdResponse(["id" => $reservation->getId()]);
    }

    /**
     * @Route("/pending", methods={"get"})
     */
    public function pending()
    {
        $reservations = $this->reservationRepository->findPendingByUser(
            $this->getUser()->getId(),
            $this->contextService->getLocation()->getId()
        );

        foreach ($reservations as &$reservation) {
            $dateTime = \DateTime::createFromFormat("Y-m-d H:i:s", $reservation["date"]);
            $reservation["date"] = $dateTime->format("Y-m-d");
        }

        return $this->json($reservations);
    }

    /**
     * @Route("/pending/count", methods={"get"})
     */
    public function countPending()
    {
        $reservations = $this->reservationRepository->countPendingByUser(
            $this->getUser()->getId(),
            $this->contextService->getLocation()->getId()
        );

        return $this->okResponse($reservations);
    }

    /**
     * @Route("/{id}", methods={"delete"})
     */
    public function delete(int $id)
    {
        /**
         * @var Reservation $reservation
         */
        $reservation = $this->reservationRepository->find($id);

        if (!$reservation) {
            return $this->resourceNotFoundResponse("Reservation", $id);
        }

        if ($reservation->getUser()->getId() === $this->getUser()) {
            return $this->unauthorizedResponse();
        }

        $this->entityManager->remove($reservation);
        $this->entityManager->flush();

        return $this->noContentResponse();
    }

    /**
     * @Route("/rooms/{date}", methods={"get"})
     */
    public function rooms(string $date = null)
    {
        $this->denyAccessUnlessGranted($this->contextService->getLocationRole(User::ROLE_ADMIN));

        $current = new \DateTimeImmutable();
        $scheduleDay = $date ? \DateTime::createFromFormat("Y-m-d", $date) : $current;

        if (!$scheduleDay) {
            return $this->badRequestResponse("Failed to parse date string '${date}'");
        }

        $locationId = $this->contextService->getLocation()->getId();

        // calculate hash and only re-fetch db if it changed
        $checksum = $this->reservationRepository->getLocationChecksum($locationId);

        if ($this->redis->get("rooms-checksum") === $checksum) {
            return $this->okResponse(json_decode($this->redis->get("rooms"), true));
        }

        $rooms = $this->roomRepository->all($locationId);

        foreach ($rooms as &$room) {
            $roomId = $room["id"];

            $room["schedule"] = $this->timeSlotRepository->findByLocationAndRoom(
                $locationId,
                $roomId,
                strtolower($scheduleDay->format("l"))
            );

            foreach ($room["schedule"] as &$timeSlot) {

                $hash = Reservation::buildHash(
                    $roomId,
                    $locationId,
                    $timeSlot["start_time"],
                    $timeSlot["end_time"],
                    $scheduleDay->format('Y-m-d')
                );

                $timeSlot["reservations"] = $this->reservationRepository->findReservations($hash);

                $timeSlot["available"] = $timeSlot["capacity"] - count($timeSlot["reservations"]);
                $timeSlot["hash"] = $hash;
            }
        }

        $this->redis->set("rooms-checksum", $checksum);
        $this->redis->set("rooms", json_encode($rooms));

        return $this->okResponse($rooms);
    }

    /**
     * @Route("/{id}", methods={"put"})
     */
    public function update(Request $request, string $id)
    {
        $this->denyAccessUnlessGranted($this->contextService->getLocationRole(User::ROLE_ADMIN));

        /**
         * @var Reservation $reservation
         */
        $reservation = $this->reservationRepository->find($id);

        if (!$reservation) {
            return $this->resourceNotFoundResponse("Reservation", $id);
        }

        $form = $this->createFormBuilder($reservation, ["csrf_protection" => false])
            ->add("appeared", CheckboxType::class, [
                "constraints" => [new NotNull()]
            ])
            ->getForm();


        $form->submit(self::decodePayLoad($request), false);

        if (!$form->isValid()) {
            return $this->badFormRequestResponse($form);
        }

        $this->entityManager->persist($reservation);
        $this->entityManager->flush();

        return $this->noContentResponse();
    }

}