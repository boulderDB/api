<?php

namespace App\Serializer;

use App\Entity\HoldType;
use App\Service\SerializerInterface;

class HoldTypeSerializer implements SerializerInterface
{
    public function serialize($class, array $groups = [], array $arguments = []): array
    {
        /**
         * @var HoldType $class
         */
        return [
            "id" => $class->getId(),
            "name" => $class->getName(),
            "image" => $class->getImage()
        ];
    }
}
