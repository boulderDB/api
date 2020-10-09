<?php

namespace App\Serializer;

use App\Entity\Reservation;

class ReservationSerializer
{
    public static function serialize(Reservation $reservation)
    {
        return [
            "id" => $reservation->getId(),
            "first_name" => $reservation->getFirstName(),
            "last_name" => $reservation->getLastName(),
            "username" => $reservation->getUsername(),
            "appeared" => $reservation->getAppeared(),
            "quantity" => $reservation->getQuantity()
        ];
    }
}