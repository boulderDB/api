<?php

namespace App\Serializer;

use App\Entity\User;

class UserSerializer
{
    public static function serialize(User $user)
    {
        return [
            'id' => $user->getId(),
            'media' => $user->getMedia(),
            'visible' => $user->isVisible(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'armSpan' => $user->getArmSpan(),
            'height' => $user->getHeight(),
            'weight' => $user->getWeight(),
            'apeIndex' => $user->getApeIndex(),
            'gender' => $user->getGender(),
        ];
    }
}