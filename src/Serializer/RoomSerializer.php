<?php

namespace App\Serializer;

use App\Entity\Room;
use App\Service\SerializerInterface;

class RoomSerializer implements SerializerInterface
{
    public function serialize($class, array $groups = [], array $arguments = []): array
    {
        /**
         * @var Room $class
         */
        return [
            "id" => $class->getId(),
            "name" => $class->getName(),
        ];
    }
}
