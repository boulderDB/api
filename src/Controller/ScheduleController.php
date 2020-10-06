<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\TimeSlot;
use App\Entity\TimeSlotExclusion;
use App\Helper\TimeHelper;
use App\Helper\TimeSlotHelper;
use App\Repository\ReservationRepository;
use App\Repository\RoomRepository;
use App\Repository\TimeSlotExclusionRepository;
use App\Repository\TimeSlotRepository;
use App\Service\ContextService;
use Carbon\Carbon;
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
    private TimeSlotExclusionRepository $timeSlotExclusionRepository;
    private TimeSlotHelper $timeSlotHelper;

    public function __construct(
        ContextService $contextService,
        EntityManagerInterface $entityManager,
        ReservationRepository $reservationRepository,
        RoomRepository $roomRepository,
        TimeSlotRepository $timeSlotRepository,
        TimeSlotExclusionRepository $timeSlotExclusionRepository,
        TimeSlotHelper $timeSlotHelper
    )
    {
        $this->contextService = $contextService;
        $this->entityManager = $entityManager;
        $this->reservationRepository = $reservationRepository;
        $this->roomRepository = $roomRepository;
        $this->timeSlotRepository = $timeSlotRepository;
        $this->timeSlotExclusionRepository = $timeSlotExclusionRepository;
        $this->timeSlotHelper = $timeSlotHelper;
    }

    /**
     * @Route("/{roomId}/{ymd}", methods={"get"})
     */
    public function day(Request $request, string $roomId, string $ymd = null)
    {
        $userId = $this->getUser()->getId();

        if (!$this->roomRepository->exists($roomId)) {
            return $this->resourceNotFoundResponse("Room", $roomId);
        }

        $scheduleDate = $ymd ? Carbon::createFromFormat(TimeHelper::DATE_FORMAT_DATE, $ymd)->startOfDay() : Carbon::create()->startOfDay();

        if ($scheduleDate->isBefore(Carbon::create())) {
            return $this->resourceNotFoundResponse("Schedule");
        }

        if (!$scheduleDate) {
            return $this->json([
                "message" => "Failed to parse date string '${$ymd}'",
                "code" => Response::HTTP_BAD_REQUEST
            ], Response::HTTP_BAD_REQUEST);
        }

        $timeSlots = $this->timeSlotRepository->getForRoomAndDayName($roomId, $scheduleDate->format("l"));

        /**
         * @var TimeSlot[] $timeSlots
         */
        foreach ($timeSlots as $timeSlot) {
            $this->timeSlotHelper->appendData($timeSlot, $ymd);
        }

        $exclusions = $this->timeSlotExclusionRepository->getPendingForRoomAndDate($roomId, $scheduleDate->toDateTime());

        foreach ($timeSlots as $timeSlot) {
            TimeSlotHelper::calculateAvailable($timeSlot, $exclusions);
        }

        $data = array_map(function ($timeSlot) use ($userId) {

            /**
             * @var Reservation $reservation
             * @var Reservation $userReservation
             * @var TimeSlot $timeSlot
             */
            $userReservation = $timeSlot->getReservations()->filter(function ($reservation) use ($userId) {
                /**
                 * @var Reservation $reservation
                 */
                return $reservation->getUser()->getId() === $userId;

            })->first();

            return [
                "hash" => $timeSlot->getHashId(),
                "available" => $timeSlot->getAvailable(),
                "capacity" => $timeSlot->getCapacity(),
                "start_time" => $timeSlot->getStartTime(),
                "end_time" => $timeSlot->getEndTime(),
                "reservation" => $userReservation ? $userReservation->getId() : null,
            ];

        }, $timeSlots);

        return $this->okResponse($data);
    }
}