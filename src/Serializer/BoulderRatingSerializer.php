<?php

namespace App\Serializer;

use App\Entity\BoulderRating;

class BoulderRatingSerializer
{
    public static function serialize($class, array $groups = [], array $arguments = [])
    {
        /**
         * @var BoulderRating $class
         */
        return [
            "id" => $class->getId(),
            "rating" => $class->getRating()
        ];
    }
}