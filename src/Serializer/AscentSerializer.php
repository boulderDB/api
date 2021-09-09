<?php

namespace App\Serializer;

use App\Entity\Ascent;
use App\Service\SerializerInterface;

class AscentSerializer implements SerializerInterface
{
    public function serialize($class, array $groups = [], array $arguments = []): array
    {
        /**
         * @var Ascent $class
         */
        return [
            "boulderId" => $class->getBoulder()->getId(),
            "points" => $class->getScore(),
            "ascents" => $class->getBoulder()->getAscents()->count(),
            "me" => [
                "id" => $class->getId(),
                "type" => $class->getType(),
                "userId" => $class->getOwner()->getId()
            ]
        ];
    }
}
