<?php


namespace App\Serializer;

use App\Entity\Label;
use App\Service\SerializerInterface;

class LabelSerializer implements SerializerInterface
{
    public function serialize($class, array $groups = [], array $arguments = []): array
    {
        /**
         * @var Label $class
         */
        return [
            "id " => $class->getId(),
            "name" => $class->getName()
        ];
    }
}
