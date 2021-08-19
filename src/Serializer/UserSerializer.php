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
            "username" => $class->getUsername(),
        ];

        if (in_array(self::GROUP_DETAIL, $groups)) {
            $data["email"] = $class->getEmail();
            $data["image"] = $class->getImage();
            $data["visible"] = $class->isVisible();
            $data["firstName"] = $class->getFirstName();
            $data["lastName"] = $class->getLastName();

            $data["notifications"] = array_map(function ($notification) {
                /**
                 * @var \App\Entity\Notification $notification
                 */
                return Serializer::serialize($notification);

            }, $class->getNotifications()->toArray());
        }

        if (in_array(self::GROUP_ADMIN, $groups)) {
            $data["roles"] = $class->getRoles();
        }

        return $data;
    }
}
