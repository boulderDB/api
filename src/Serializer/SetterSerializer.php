<?php

namespace App\Serializer;

use App\Entity\Setter;
use App\Service\SerializerInterface;

class SetterSerializer implements SerializerInterface
{
    public function serialize($class, array $groups = [], array $arguments = []): array
    {
        /**
         * @var Setter $class
         */
        return [
            "id" => $class->getId(),
            "username" => $class->getUsername(),
        ];
    }
}
