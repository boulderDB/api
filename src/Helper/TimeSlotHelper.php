<?php

namespace App\Helper;

use App\Entity\Reservation;
use App\Entity\TimeSlot;
use App\Entity\TimeSlotExclusion;
use App\Repository\ReservationRepository;
use Carbon\Carbon;

class TimeSlotHelper
{
    private ReservationRepository $reservationRepository;

    public function __construct(ReservationRepository $reservationRepository)
    {
        $this->reservationRepository = $reservationRepository;
    }

    public function appendData(TimeSlot $timeSlot, string $ymd): void
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

    public static function calculateAvailable(TimeSlot $timeSlot, array $exclusions): void
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