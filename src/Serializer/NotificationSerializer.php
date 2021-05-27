<?php

namespace App\Serializer;

use App\Service\SerializerInterface;

class NotificationSerializer implements SerializerInterface
{
    public function serialize($class, array $groups = [], array $arguments = []): array
    {
        /**
         * @var \App\Entity\Notification $class
         */
        return [
            "id" => $class->getId(),
            "type" => $class->getType(),
            "location" => $class->getLocation()->getUrl(),
            "active" => $class->isActive()
        ];
    }
}