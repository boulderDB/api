<?php

namespace App\Helper;

use App\Entity\Reservation;
use App\Entity\TimeSlot;
use App\Entity\TimeSlotExclusion;
use App\Repository\ReservationRepository;
use App\Repository\TimeSlotExclusionRepository;
use App\Repository\TimeSlotRepository;
use Carbon\Carbon;

class ScheduleHelper
{
    private TimeSlotRepository $timeSlotRepository;
    private TimeSlotExclusionRepository $timeSlotExclusionRepository;
    private ReservationRepository $reservationRepository;

    public function __construct(
        TimeSlotRepository $timeSlotRepository,
        TimeSlotExclusionRepository $timeSlotExclusionRepository,
        ReservationRepository $reservationRepository
    )
    {
        $this->timeSlotRepository = $timeSlotRepository;
        $this->timeSlotExclusionRepository = $timeSlotExclusionRepository;
        $this->reservationRepository = $reservationRepository;
    }

    public function room(int $roomId, Carbon $scheduleDate): array
    {
        $timeSlots = $this->timeSlotRepository->getForRoomAndDayName($roomId, $scheduleDate->format("l"));

        /**
         * @var TimeSlot[] $timeSlots
         */
        foreach ($timeSlots as $timeSlot) {
            $this->appendData($timeSlot, $scheduleDate->format(TimeHelper::DATE_FORMAT_DATE));
        }

        $exclusions = $this->timeSlotExclusionRepository->getPendingForRoomAndDate($roomId, $scheduleDate->toDateTime());

        foreach ($timeSlots as $timeSlot) {
            self::calculateAvailable($timeSlot, $exclusions);
        }

        return $timeSlots;
    }

    private function appendData(TimeSlot $timeSlot, string $ymd): void
    {
        $scheduleDate = Carbon::createFromFormat(TimeHelper::DATE_FORMAT_DATE, $ymd)->startOfDay();
        $hash = $timeSlot->buildReservationHash($scheduleDate);

        $reservations = $this->reservationRepository->findBy(
            ["hashId" => $hash]
        );

        $timeSlot->initReservations($reservations);
        $timeSlot->buildStartDate($ymd);
        $timeSlot->buildEndDate($ymd);
        $timeSlot->setHashId($hash);
    }

    private static function calculateAvailable(TimeSlot $timeSlot, array $exclusions): void
    {
        $blocked = 0;

        /**
         * @var Reservation $reservation
         */
        foreach ($timeSlot->getReservations() as $reservation) {
            $blocked += $reservation->getQuantity();
        }

        /**
         * @var TimeSlotExclusion[] $exclusions
         */
        foreach ($exclusions as $exclusion) {
            if (!$exclusion->intersectsTimeSlot($timeSlot)) {
                continue;
            }

            $blocked += $exclusion->getQuantity() ? $exclusion->getQuantity() : $timeSlot->getCapacity();
        }

        $timeSlot->setBlocked($blocked);
        $timeSlot->setAvailable($timeSlot->getCapacity() - $blocked);
    }
}