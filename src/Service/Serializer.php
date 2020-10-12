<?php

namespace App\Service;

use App\Entity\TimeSlot;
use App\Entity\User;
use App\Serializer\TimeSlotSerializer;
use App\Serializer\UserSerializer;

class Serializer
{
    private static array $serializers = [
        TimeSlot::class => TimeSlotSerializer::class,
        User::class => UserSerializer::class
    ];

    public static function serialize($any, array $groups = [SerializerInterface::GROUP_INDEX], array $arguments = []): array
    {
        if (is_array($any)) {
            return array_map(function ($item) use ($groups, $arguments) {
                return self::resolveSerializer(get_class($item))->serialize($item, $groups, $arguments);
            }, $any);
        }

        return self::resolveSerializer(get_class($any))->serialize($any, $groups, $arguments);
    }

    public static function formatDate(\DateTimeInterface $dateTime = null): ?string
    {
        if (!$dateTime) {
            return null;
        }

        return $dateTime->format("c");
    }

    private static function resolveSerializer($class): SerializerInterface
    {
        if (!array_key_exists($class, static::$serializers)) {
            throw new \InvalidArgumentException("Serializer for class $class not configured");
        }

        $serializerClass = static::$serializers[$class];


        if (!$serializerClass instanceof SerializerInterface) {
            static::$serializers[$class] = new $serializerClass;
        }

        return static::$serializers[$class];
    }
}