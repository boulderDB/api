<?php

namespace App\Serializer;

use App\Entity\User;
use App\Service\Serializer;
use App\Service\SerializerInterface;

class UserSerializer implements SerializerInterface
{
    public function serialize($class, array $groups = [], array $arguments = []): array
    {
        /**
         * @var User $class
         */
        $data = [
            "id" => $class->getId(),
            "image" => $class->getImage(),
            "visible" => $class->isVisible(),
            "username" => $class->getUsername(),
            "firstName" => $class->getFirstName(),
            "lastName" => $class->getLastName(),
            "notifications" => array_map(function ($notification) {
                /**
                 * @var \App\Entity\Notification $notification
                 */
                return Serializer::serialize($notification);

            }, $class->getNotifications()->toArray())
        ];

        if (in_array(self::GROUP_DETAIL, $groups)) {
            $data["email"] = $class->getEmail();
        }

        return $data;
    }
}
