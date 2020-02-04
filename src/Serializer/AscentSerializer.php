<?php

namespace App\Serializer;

use App\Entity\Ascent;

class AscentSerializer
{
    public static function serialize(Ascent $ascent)
    {
        return [
            'boulderId' => $ascent->getBoulder()->getId(),
            'points' => $ascent->getScore(),
            'ascents' => $ascent->getBoulder()->getAscents()->count(),
            'me' => [
                'id' => $ascent->getId(),
                'type' => $ascent->getType(),
                'userId' => $ascent->getUser()->getId()
            ]
        ];
    }
}