<?php

namespace App\Serializer;

use App\Entity\BoulderRating;

class BoulderRatingSerializer
{
    public static function serialize(BoulderRating $rating)
    {
        return [
            "id" => $rating->getId(),
            "rating" => $rating->getRating()
        ];
    }
}