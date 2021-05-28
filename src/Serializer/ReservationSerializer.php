<?php

namespace App\Serializer;

use App\Entity\Reservation;
use App\Service\SerializerInterface;

class ReservationSerializer implements SerializerInterface
{
    public function serialize($class, array $groups = [], array $arguments = []): array
    {
        /**
         * @var Reservation $class
         */
        return [
            "id" => $class->getId(),
            "first_name" => $class->getFirstName(),
            "last_name" => $class->getLastName(),
            "username" => $class->getUsername(),
            "appeared" => $class->getAppeared(),
            "checked_in" => $class->getCheckedIn(),
            "quantity" => $class->getQuantity()
        ];
    }
}
