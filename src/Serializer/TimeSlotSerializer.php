<?php

namespace App\Serializer;

use App\Entity\TimeSlot;
use App\Service\SerializerInterface;

class TimeSlotSerializer implements SerializerInterface
{
    public const GROUP_COMPUTED = "computed";

    public function serialize($class, array $groups = [], array $arguments = []): array
    {
        /**
         * @var TimeSlot $class
         */
        $data = [
            "id" => $class->getId(),
            "capacity" => $class->getCapacity(),
            "allow_quantity" => $class->getAllowQuantity(),
            "day_name" => $class->getDayName(),
            "start_time" => $class->getStartTime(),
            "end_time" => $class->getEndTime(),
        ];

        if (in_array(self::GROUP_DETAIL, $groups)) {
            $data["reservations"] = array_map(function ($reservation) {
                return ReservationSerializer::serialize($reservation);

            }, $class->getReservations()->toArray());
        }

        if (in_array(self::GROUP_COMPUTED, $groups)) {
            $data["hash"] = $class->getHashId();
            $data["available"] = $class->getAvailable();
        }

        if (isset($arguments["userId"])) {
            $userReservation = $class->getUserReservation($arguments["userId"]);

            $data["reservation"] = $userReservation ? $userReservation->getId() : null;
        }

        return $data;
    }
}