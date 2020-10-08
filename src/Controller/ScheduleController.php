<?php

namespace App\Controller;

use App\Entity\Room;
use App\Entity\TimeSlot;
use App\Helper\ScheduleHelper;
use App\Helper\TimeHelper;
use App\Repository\RoomRepository;
use App\Serializer\TimeSlotSerializer;
use App\Service\ContextService;
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
    private ScheduleHelper $scheduleHelper;
    private ContextService $contextService;

    public function __construct(
        RoomRepository $roomRepository,
        ScheduleHelper $scheduleHelper,
        ContextService $contextService
    )
    {
        $this->roomRepository = $roomRepository;
        $this->scheduleHelper = $scheduleHelper;
        $this->contextService = $contextService;
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

        $schedule = $this->scheduleHelper->room($roomId, $scheduleDate);
        $adminView = $this->isLocationAdmin() && $request->query->get("admin");

        return $this->okResponse(array_map(function ($timeSlot) use ($userId, $adminView) {

                /**
                 * @var TimeSlot $timeSlot
                 */
                return TimeSlotSerializer::serialize($timeSlot, $userId, $adminView);

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
                return TimeSlotSerializer::serialize($timeSlot, null, true);

            }, $this->scheduleHelper->room($room->getId(), $scheduleDate));

            return $roomData;
        }, $rooms));
    }
}