<?php

namespace App\Serializer;

use App\Entity\Wall;
use App\Service\SerializerInterface;

class WallSerializer implements SerializerInterface
{
    public function serialize($class, array $groups = [], array $arguments = []): array
    {
        /**
         * @var Wall $class
         */
        return [
            "id" => $class->getId(),
            "name" => $class->getName(),
        ];
    }
}
