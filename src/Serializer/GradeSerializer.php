<?php

namespace App\Serializer;

use App\Entity\Grade;
use App\Service\SerializerInterface;

class GradeSerializer implements SerializerInterface
{
    public function serialize($class, array $groups = [], array $arguments = []): array
    {
        /**
         * @var Grade $class
         */
        return [
            "id" => $class->getId(),
            "name" => $class->getName(),
        ];
    }
}
