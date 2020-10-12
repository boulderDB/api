<?php

namespace App\Serializer;

use App\Entity\User;
use App\Service\SerializerInterface;

class UserSerializer implements SerializerInterface
{
    public function serialize($class, array $groups = [], array $arguments = []): array
    {
        /**
         * @var User $class
         */
        return [
            "id" => $class->getId(),
            "media" => $class->getMedia(),
            "visible" => $class->isVisible(),
            "username" => $class->getUsername(),
            "firstName" => $class->getFirstName(),
            "lastName" => $class->getLastName(),
        ];
    }
}