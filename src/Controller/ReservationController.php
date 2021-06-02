<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\TimeSlot;
use App\Form\ReservationType;
use App\Helper\TimeSlotHelper;
use App\Repository\ReservationRepository;
use App\Repository\RoomRepository;
use App\Repository\TimeSlotExclusionRepository;
use App\Repository\TimeSlotRepository;
use App\Entity\User;
use App\Factory\RedisConnectionFactory;
use App\Service\ContextService;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
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
    use ContextualizedControllerTrait;

    private const PENDING_RESERVATION_LIMIT = 2;

    private EntityManagerInterface $entityManager;
    private ContextService $contextService;
    private TimeSlotRepository $timeSlotRepository;
    private ReservationRepository $reservationRepository;
    private RoomRepository $roomRepository;
    private TimeSlotHelper $timeSlotHelper;
    private TimeSlotExclusionRepository $timeSlotExclusionRepository;
    private \Redis $redis;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContextService $contextService,
        TimeSlotRepository $timeSlotRepository,
        ReservationRepository $reservationRepository,
        RoomRepository $roomRepository,
        TimeSlotHelper $timeSlotHelper,
        TimeSlotExclusionRepository $timeSlotExclusionRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->contextService = $contextService;
        $this->timeSlotRepository = $timeSlotRepository;
        $this->reservationRepository = $reservationRepository;
        $this->roomRepository = $roomRepository;
        $this->timeSlotHelper = $timeSlotHelper;
        $this->timeSlotExclusionRepository = $timeSlotExclusionRepository;

        $this->redis = RedisConnectionFactory::create();
    }

    /**
     * @Route("/guest", methods={"post"})
     */
    public function guest(Request $request)
    {
        $this->denyUnlessLocationAdmin();

        $reservation = new Reservation();

        $form = $this->createForm(ReservationType::class, $reservation);
        $form
            ->add(...ReservationType::getEmailField())
            ->add(...ReservationType::getFirstNameField())
            ->add(...ReservationType::getLastNameField());

        $form->submit(self::decodePayLoad($request));

        if (!$form->isValid()) {
            return $this->badFormRequestResponse($form);
        }

        $reservation->generateHashId();

        /**
         * @var TimeSlot $timeSlot
         */
        $timeSlot = $this->timeSlotRepository->findOneBy([
            "room" => $reservation->getRoom()->getId(),
            "startTime" => $reservation->getStartTime(),
            "endTime" => $reservation->getEndTime(),
            "dayName" => $reservation->getDayName()
        ]);

        if (!$timeSlot) {
            return $this->resourceNotFoundResponse("TimeSlot");
        }

        $exclusions = $this->timeSlotExclusionRepository->getPendingForRoomAndDate(
            $reservation->getRoom()->getId(),
            $reservation->getDate()
        );

        $this->timeSlotHelper->appendData(
            $timeSlot,
            $reservation->getDate()->format("Y-m-d"),
            $exclusions
        );

        if ($timeSlot->getEndDate() < Carbon::now()) {
            return $this->json([
                "message" => "This time slot is expired.",
                "code" => Response::HTTP_CONFLICT
            ], Response::HTTP_CONFLICT);
        }

        if ($timeSlot->getCapacity() <= 0) {
            return $this->json([
                "message" => "This time slot is full.",
                "code" => Response::HTTP_CONFLICT
            ], Response::HTTP_CONFLICT);
        }

        if ($reservation->getQuantity() > $timeSlot->getMaxQuantity()) {
            return $this->json([
                "message" => "This time slot only allows a quantity of {$timeSlot->getMaxQuantity()} per reservation.",
                "code" => Response::HTTP_CONFLICT
            ], Response::HTTP_CONFLICT);
        }

        $this->entityManager->persist($reservation);
        $this->entityManager->flush();

        return $this->createdResponse($reservation);
    }

    /**
     * @Route(methods={"post"})
     */
    public function create(Request $request)
    {
        $reservation = new Reservation();
        $reservation->setUser($this->getUser());

        $form = $this->createForm(ReservationType::class, $reservation);
        $form->submit(self::decodePayLoad($request));

        if (!$form->isValid()) {
            return $this->badFormRequestResponse($form);
        }

        $reservation->generateHashId();

        if ($this->reservationRepository->hasPendingReservationForTimeSlot($reservation)) {
            return $this->json([
                "message" => "You already have a pending reservation for this time slot.",
                "code" => Response::HTTP_CONFLICT
            ], Response::HTTP_CONFLICT);
        }

        if ($this->reservationRepository->hasPendingReservationForDate($reservation)) {
            return $this->json([
                "message" => "You already have a reservation for this day.",
                "code" => Response::HTTP_CONFLICT
            ], Response::HTTP_CONFLICT);
        }

        if ($this->reservationRepository->countPendingReservations($reservation) >= self::PENDING_RESERVATION_LIMIT) {
            $limit = self::PENDING_RESERVATION_LIMIT;

            return $this->json([
                "message" => "You exceeded limit of $limit pending reservations",
                "code" => Response::HTTP_CONFLICT
            ], Response::HTTP_CONFLICT);
        }

        /**
         * @var TimeSlot $timeSlot
         */
        $timeSlot = $this->timeSlotRepository->findOneBy([
            "room" => $reservation->getRoom()->getId(),
            "startTime" => $reservation->getStartTime(),
            "endTime" => $reservation->getEndTime(),
            "dayName" => $reservation->getDayName()
        ]);

        if (!$timeSlot) {
            return $this->resourceNotFoundResponse("TimeSlot");
        }

        $exclusions = $this->timeSlotExclusionRepository->getPendingForRoomAndDate(
            $reservation->getRoom()->getId(),
            $reservation->getDate()
        );

        $this->timeSlotHelper->appendData(
            $timeSlot,
            $reservation->getDate()->format("Y-m-d"),
            $exclusions
        );

        if ($timeSlot->getEndDate() < Carbon::now()) {
            return $this->json([
                "message" => "This time slot is expired.",
                "code" => Response::HTTP_CONFLICT
            ], Response::HTTP_CONFLICT);
        }

        if ($timeSlot->getAvailable() <= 0) {
            return $this->json([
                "message" => "This time slot is full.",
                "code" => Response::HTTP_CONFLICT
            ], Response::HTTP_CONFLICT);
        }

        if ($timeSlot->getAvailable() < $reservation->getQuantity()) {
            return $this->json([
                "message" => "Your quantity exceeds the timeslot capacity",
                "code" => Response::HTTP_CONFLICT
            ], Response::HTTP_CONFLICT);
        }

        if ($reservation->getQuantity() > $timeSlot->getMaxQuantity()) {
            return $this->json([
                "message" => "This time slot only allows a quantity of {$timeSlot->getMaxQuantity()} per reservation.",
                "code" => Response::HTTP_CONFLICT
            ], Response::HTTP_CONFLICT);
        }

        if ($reservation->getQuantity() < $timeSlot->getMinQuantity()) {
            return $this->json([
                "message" => "This time slot only requires a quantity of {$timeSlot->getMaxQuantity()} per reservation.",
                "code" => Response::HTTP_CONFLICT
            ], Response::HTTP_CONFLICT);
        }

        $this->entityManager->persist($reservation);
        $this->entityManager->flush();

        return $this->createdResponse($reservation);
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

        if (!$reservation->getUser() && !$this->isLocationAdmin()) {
            return $this->unauthorizedResponse();
        }

        if ($reservation->getUser() && $reservation->getUser()->getId() !== $this->getUser()->getId() && !$this->isLocationAdmin()) {
            return $this->unauthorizedResponse();
        }

        $this->entityManager->remove($reservation);
        $this->entityManager->flush();

        return $this->noContentResponse();
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

        if (!$reservation->getUser() !== $this->getUser()) {
            return $this->unauthorizedResponse();
        }

        $form = $this->createFormBuilder($reservation, ["csrf_protection" => false])
            ->add("checkedIn", CheckboxType::class, [
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


    /**
     * @Route("/no-shows", methods={"get"})
     */
    public function listNoShows()
    {
        $this->denyAccessUnlessGranted($this->contextService->getLocationRole(User::ROLE_ADMIN));

        return $this->json($this->reservationRepository->findNoShows());
    }

}
