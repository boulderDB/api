<?php

namespace App\Helper;

use App\Entity\Reservation;
use App\Entity\TimeSlot;
use App\Entity\TimeSlotExclusion;
use App\Repository\ReservationRepository;
use App\Repository\TimeSlotExclusionRepository;
use App\Repository\TimeSlotRepository;
use Carbon\Carbon;

class TimeSlotHelper
{
    private ReservationRepository $reservationRepository;
    private TimeSlotExclusionRepository $timeSlotExclusionRepository;
    private TimeSlotRepository $timeSlotRepository;

    public function __construct(
        ReservationRepository $reservationRepository,
        TimeSlotExclusionRepository $timeSlotExclusionRepository,
        TimeSlotRepository $timeSlotRepository
    )
    {
        $this->reservationRepository = $reservationRepository;
        $this->timeSlotExclusionRepository = $timeSlotExclusionRepository;
        $this->timeSlotRepository = $timeSlotRepository;
    }

    public function room(int $roomId, Carbon $scheduleDate, array $pendingExclusions): array
    {
        $timeSlots = $this->timeSlotRepository->getForRoomAndDayName($roomId, $scheduleDate->format("l"));

        /**
         * @var TimeSlot[] $timeSlots
         */
        foreach ($timeSlots as $timeSlot) {

            $this->appendData(
                $timeSlot,
                $scheduleDate->format(TimeHelper::DATE_FORMAT_DATE),
                $pendingExclusions
            );
        }

        foreach ($timeSlots as $timeSlot) {
            self::calculate($timeSlot, $pendingExclusions);
        }

        return $timeSlots;
    }

    public function appendData(TimeSlot $timeSlot, string $ymd, array $pendingExclusions): void
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

        self::calculate($timeSlot, $pendingExclusions);
    }

    private static function calculate(TimeSlot $timeSlot, array $exclusions): void
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
