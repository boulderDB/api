<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Room;
use App\Entity\TimeSlot;
use App\Helper\timeSlotHelper;
use App\Helper\TimeHelper;
use App\Repository\RoomRepository;
use App\Repository\TimeSlotExclusionRepository;
use App\Serializer\TimeSlotSerializer;
use App\Service\ContextService;
use App\Service\Serializer;
use App\Service\SerializerInterface;
use Carbon\Carbon;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/schedule")
 */
class ScheduleController extends AbstractController
{
    use ResponseTrait;
    use ContextualizedControllerTrait;

    private RoomRepository $roomRepository;
    private TimeSlotHelper $timeSlotHelper;
    private ContextService $contextService;
    private TimeSlotExclusionRepository $timeSlotExclusionRepository;

    public function __construct(
        RoomRepository $roomRepository,
        TimeSlotHelper $timeSlotHelper,
        ContextService $contextService,
        TimeSlotExclusionRepository $timeSlotExclusionRepository
    )
    {
        $this->roomRepository = $roomRepository;
        $this->timeSlotHelper = $timeSlotHelper;
        $this->contextService = $contextService;
        $this->timeSlotExclusionRepository = $timeSlotExclusionRepository;
    }

    /**
     * @Route("/{roomId}/{ymd}", methods={"get"}, requirements={"roomId": "\d+"})
     */
    public function day(Request $request, string $roomId, string $ymd = null)
    {
        $userId = $this->getUser()->getId();

        if (!$this->roomRepository->exists($roomId)) {
            return $this->resourceNotFoundResponse("Room", $roomId);
        }

        $scheduleDate = $ymd ? Carbon::createFromFormat(TimeHelper::DATE_FORMAT_DATE, $ymd)->startOfDay() : Carbon::now()->startOfDay();

        if ($scheduleDate->isBefore(Carbon::yesterday())) {
            return $this->resourceNotFoundResponse("Schedule");
        }

        if (!$scheduleDate) {
            return $this->badRequestResponse("Failed to parse date string '${$ymd}'");
        }

        $exclusions = $this->timeSlotExclusionRepository->getPendingForRoomAndDate(
            $roomId,
            $scheduleDate->toDateTime()
        );

        $schedule = $this->timeSlotHelper->room($roomId, $scheduleDate, $exclusions);
        $isAdmin = $this->isLocationAdmin() && $request->query->get("admin");

        return $this->okResponse(array_map(function ($timeSlot) use ($userId, $isAdmin) {

                /**
                 * @var TimeSlot $timeSlot
                 */
                return Serializer::serialize(
                    $timeSlot,
                    [
                        TimeSlotSerializer::GROUP_COMPUTED,
                        $isAdmin ? SerializerInterface::GROUP_INDEX : null
                    ],
                    [
                        "userId" => $userId
                    ]
                );

            }, $schedule)
        );
    }

    /**
     * @Route("/rooms/{ymd}", methods={"get"})
     */
    public function rooms(Request $request, string $ymd = null)
    {
        $this->denyUnlessLocationAdmin();

        $scheduleDate = $ymd ? Carbon::createFromFormat(TimeHelper::DATE_FORMAT_DATE, $ymd)->startOfDay() : Carbon::now()->startOfDay();

        if (!$scheduleDate) {
            return $this->badRequestResponse("Failed to parse date string '${$ymd}'");
        }

        $rooms = $this->roomRepository->findBy([
            "location" => $this->contextService->getLocation()->getId()
        ]);

        return $this->json(array_map(function ($room) use ($scheduleDate) {

            /**
             * @var Room $room
             */
            $roomData = [
                "id" => $room->getId(),
                "name" => $room->getName()
            ];

            $roomData["schedule"] = array_map(function ($timeSlot) {

                /**
                 * @var TimeSlot $timeSlot
                 */
                return Serializer::serialize(
                    $timeSlot,
                    [
                        SerializerInterface::GROUP_DETAIL,
                        TimeSlotSerializer::GROUP_COMPUTED
                    ]
                );

            }, $this->timeSlotHelper->room($room->getId(), $scheduleDate));

            return $roomData;
        }, $rooms));
    }

    /**
     * @Route("/allocation/{ymd}", methods={"get"})
     */
    public function currentAllocation(string $ymd = null)
    {
        $scheduleDate = $ymd ? Carbon::createFromFormat(TimeHelper::DATE_FORMAT_DATE, $ymd)->startOfDay() : Carbon::now()->startOfDay();

        if (!$scheduleDate) {
            return $this->badRequestResponse("Failed to parse date string '${$ymd}'");
        }

        $rooms = $this->roomRepository->findBy([
            "location" => $this->contextService->getLocation()->getId()
        ]);

        $current = Carbon::now();
        $current->modify("+2hours");

        return $this->json(array_map(function ($room) use ($scheduleDate, $current) {

            /**
             * @var Room $room
             */
            $data = [
                "name" => $room->getName(),
                "capacity" => 0,
                "available" => 0,
                "appeared" => 0,
                "matched_time_slots" => [],
            ];

            $schedule = $this->timeSlotHelper->room($room->getId(), $scheduleDate);

            foreach ($schedule as $timeSlot) {

                /**
                 * @var TimeSlot $timeSlot
                 */
                if ($timeSlot->getStartDate() < $current && $timeSlot->getEndDate() > $current) {
                    $data["capacity"] += $timeSlot->getCapacity();
                    $data["available"] += $timeSlot->getAvailable();

                    $data["appeared"] += $timeSlot
                        ->getReservations()
                        ->filter(function ($reservation) {
                            /**
                             * @var Reservation $reservation
                             */
                            return $reservation->getAppeared();
                        })->count();

                    $timeSlotData = Serializer::serialize($timeSlot, [
                        TimeSlotSerializer::GROUP_COMPUTED,
                    ]);

                    unset($timeSlotData["id"]);

                    $data["matched_time_slots"][] = $timeSlotData;
                }
            }

            return $data;

        }, $rooms));
    }
}
