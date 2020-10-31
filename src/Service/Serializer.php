<?php

namespace App\Service;

use App\Entity\Boulder;
use App\Entity\Grade;
use App\Entity\HoldType;
use App\Entity\Location;
use App\Entity\Room;
use App\Entity\Setter;
use App\Entity\TimeSlot;
use App\Entity\User;
use App\Entity\Wall;
use App\Serializer\BoulderSerializer;
use App\Serializer\GradeSerializer;
use App\Serializer\HoldTypeSerializer;
use App\Serializer\LocationSerializer;
use App\Serializer\RoomSerializer;
use App\Serializer\SetterSerializer;
use App\Serializer\TimeSlotSerializer;
use App\Serializer\UserSerializer;
use App\Serializer\WallSerializer;

class Serializer
{
    private static array $serializers = [
        TimeSlot::class => TimeSlotSerializer::class,
        User::class => UserSerializer::class,
        Location::class => LocationSerializer::class,
        Room::class => RoomSerializer::class,
        Setter::class => SetterSerializer::class,
        Boulder::class => BoulderSerializer::class,
        Wall::class => WallSerializer::class,
        Grade::class => GradeSerializer::class,
        HoldType::class => HoldTypeSerializer::class
    ];

    public static function serialize($any, array $groups = [SerializerInterface::GROUP_INDEX], array $arguments = []): ?array
    {
        if ($any === null) {
            return null;
        }

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
        $match = null;

        foreach (self::$serializers as $target => $serializer) {

            if (!is_subclass_of($class, $target, true) && $class !== $target) {
                continue;
            }

            $serializerClass = static::$serializers[$target];

            if (!$serializerClass instanceof SerializerInterface) {
                static::$serializers[$target] = new $serializerClass;
            }

            $match = static::$serializers[$target];
        }

        if (!$match) {
            throw new \InvalidArgumentException("Serializer for class $class not configured");
        }

        return $match;
    }
}
