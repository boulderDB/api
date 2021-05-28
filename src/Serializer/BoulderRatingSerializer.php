<?php

namespace App\Serializer;

use App\Entity\BoulderRating;
use App\Service\SerializerInterface;

class BoulderRatingSerializer implements SerializerInterface
{
    public function serialize($class, array $groups = [], array $arguments = []): array
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