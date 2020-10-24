<?php

namespace App\Helper;

use App\Entity\Reservation;
use App\Entity\TimeSlot;
use App\Repository\ReservationRepository;
use App\Repository\TimeSlotExclusionRepository;
use Carbon\Carbon;

class TimeSlotHelper
{
    private ReservationRepository $reservationRepository;
    private TimeSlotExclusionRepository $timeSlotExclusionRepository;

    public function __construct(
        ReservationRepository $reservationRepository,
        TimeSlotExclusionRepository $timeSlotExclusionRepository
    )
    {
        $this->reservationRepository = $reservationRepository;
        $this->timeSlotExclusionRepository = $timeSlotExclusionRepository;
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

    public function calculateAvailable(TimeSlot $timeSlot, Reservation $reservation): void
    {
        $exclusions = $this->timeSlotExclusionRepository->getPendingForRoomAndDate(
            $reservation->getRoom()->getId(),
            $reservation->getDate()
        );

        ScheduleHelper::calculateAvailable($timeSlot, $exclusions);
    }
}
