<?php

namespace App\Serializer;

use App\Service\Serializer;
use App\Service\SerializerInterface;

class BoulderErrorSerializer implements SerializerInterface
{
    public function serialize($class, array $groups = [], array $arguments = []): array
    {
        /**
         * @var \App\Entity\BoulderError $class
         */
        return [
            "id" => $class->getId(),
            "message" => $class->getMessage(),
            "boulder" => Serializer::serialize($class->getBoulder()),
            "author" => Serializer::serialize($class->getAuthor()),
        ];
    }
}