<?php

namespace App\Serializer;

use App\Service\Serializer;
use App\Service\SerializerInterface;

class BoulderCommentSerializer implements SerializerInterface
{
    public function serialize($class, array $groups = [], array $arguments = []): array
    {
        /**
         * @var \App\Entity\BoulderComment $class
         */
        return [
            "id" => $class->getId(),
            "message" => $class->getMessage(),
            "boulder" => Serializer::serialize($class->getBoulder()),
            "author" => Serializer::serialize($class->getAuthor()),
        ];
    }
}