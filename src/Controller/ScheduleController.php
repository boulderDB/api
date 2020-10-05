<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Repository\ReservationRepository;
use App\Repository\RoomRepository;
use App\Repository\TimeSlotRepository;
use App\Service\ContextService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/schedule")
 */
class ScheduleController extends AbstractController
{
    use ResponseTrait;

    private ContextService $contextService;
    private EntityManagerInterface $entityManager;
    private ReservationRepository $reservationRepository;
    private RoomRepository $roomRepository;
    private TimeSlotRepository $timeSlotRepository;

    public function __construct(
        ContextService $contextService,
        EntityManagerInterface $entityManager,
        ReservationRepository $reservationRepository,
        RoomRepository $roomRepository,
        TimeSlotRepository $timeSlotRepository
    )
    {
        $this->contextService = $contextService;
        $this->entityManager = $entityManager;
        $this->reservationRepository = $reservationRepository;
        $this->roomRepository = $roomRepository;
        $this->timeSlotRepository = $timeSlotRepository;
    }

    /**
     * @Route("/{roomId}/{date}", methods={"get"})
     */
    public function day(Request $request, string $roomId, string $date = null)
    {
        $locationId = $this->contextService->getLocation()->getId();

        if (!$this->roomRepository->exists($roomId)) {
            return $this->resourceNotFoundResponse("Room", $roomId);
        }

        $current = new \DateTimeImmutable();

        $scheduleDay = $date ? \DateTime::createFromFormat("Y-m-d", $date) : $current;

        if ($scheduleDay < $current->modify("-1 day")) {
            return $this->logicErrorResponse("That ship has sailed, don't you think?");
        }

        if (!$scheduleDay) {
            return $this->json([
                "message" => "Failed to parse date string '${date}'",
                "code" => Response::HTTP_BAD_REQUEST
            ], Response::HTTP_BAD_REQUEST);
        }

        $schedule = $this->timeSlotRepository->findByLocationAndRoom(
            $locationId,
            $roomId,
            strtolower($scheduleDay->format("l"))
        );

        $userId = $this->getUser()->getId();

        foreach ($schedule as &$timeSlot) {

            $hash = Reservation::buildHash(
                $roomId,
                $locationId,
                $timeSlot["start_time"],
                $timeSlot["end_time"],
                $scheduleDay->format('Y-m-d')
            );

            $hashes = $this->reservationRepository->findHashes($hash);

            $timeSlot["available"] = $timeSlot["capacity"] - count($hashes);
            $timeSlot["hash"] = $hash;

            $userReservation = array_filter($hashes, function ($hash) use ($userId) {
                return $hash["user_id"] === $userId;
            });

            $timeSlot["reservation"] = $userReservation ? $userReservation[0]["id"] : null;
        }

        return $this->json($schedule);
    }
}