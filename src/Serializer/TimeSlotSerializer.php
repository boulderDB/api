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
            "min_quantity" => $class->getMinQuantity(),
            "max_quantity" => $class->getMaxQuantity(),
            "day_name" => $class->getDayName(),
            "start_time" => $class->getStartTime(),
            "end_time" => $class->getEndTime(),
            "enabled" => $class->isEnabled(),
            "auto_destroy" => $class->isAutoDestroy(),
            "enable_after" => $class->getEnableAfter(),
            "disable_after" => $class->getDisableAfter()
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
