<?php

namespace App\Serializer;

use App\Entity\TimeSlot;

class TimeSlotSerializer
{
    public static function serialize(TimeSlot $timeSlot, int $userId, bool $adminView = false)
    {
        $data = [
            "hash" => $timeSlot->getHashId(),
            "available" => $timeSlot->getAvailable(),
            "capacity" => $timeSlot->getCapacity(),
            "start_time" => $timeSlot->getStartTime(),
            "end_time" => $timeSlot->getEndTime(),
        ];

        if ($adminView) {
            $data["reservations"] = array_map(function ($reservation) {
                return ReservationSerializer::serialize($reservation);

            }, $timeSlot->getReservations()->toArray());

        } else {
            $data["reservation"] = $timeSlot->getUserReservation($userId);
        }

        return $data;
    }
}