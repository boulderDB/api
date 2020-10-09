<?php

namespace App\Serializer;

use App\Entity\User;

class UserSerializer
{
    public static function serialize(User $user)
    {
        return [
            "id" => $user->getId(),
            "media" => $user->getMedia(),
            "visible" => $user->isVisible(),
            "username" => $user->getUsername(),
            "firstName" => $user->getFirstName(),
            "lastName" => $user->getLastName(),
        ];
    }
}