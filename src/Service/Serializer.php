<?php

namespace App\Service;

use App\Entity\Ascent;
use App\Entity\Boulder;
use App\Entity\BoulderComment;
use App\Entity\BoulderError;
use App\Entity\BoulderRating;
use App\Entity\Grade;
use App\Entity\HoldType;
use App\Entity\Location;
use App\Entity\Notification;
use App\Entity\Reservation;
use App\Entity\Room;
use App\Entity\Setter;
use App\Entity\TimeSlot;
use App\Entity\User;
use App\Entity\Wall;
use App\Serializer\AscentSerializer;
use App\Serializer\BoulderCommentSerializer;
use App\Serializer\BoulderErrorSerializer;
use App\Serializer\BoulderRatingSerializer;
use App\Serializer\BoulderSerializer;
use App\Serializer\GradeSerializer;
use App\Serializer\HoldTypeSerializer;
use App\Serializer\LocationSerializer;
use App\Serializer\NotificationSerializer;
use App\Serializer\ReservationSerializer;
use App\Serializer\RoomSerializer;
use App\Serializer\SetterSerializer;
use App\Serializer\TimeSlotSerializer;
use App\Serializer\UserSerializer;
use App\Serializer\WallSerializer;

class Serializer
{
    private static array $serializers = [
        Ascent::class => AscentSerializer::class,
        BoulderComment::class => BoulderCommentSerializer::class,
        BoulderError::class => BoulderErrorSerializer::class,
        BoulderRating::class => BoulderRatingSerializer::class,
        Boulder::class => BoulderSerializer::class,
        Grade::class => GradeSerializer::class,
        HoldType::class => HoldTypeSerializer::class,
        Location::class => LocationSerializer::class,
        Notification::class => NotificationSerializer::class,
        Reservation::class => ReservationSerializer::class,
        Room::class => RoomSerializer::class,
        Setter::class => SetterSerializer::class,
        TimeSlot::class => TimeSlotSerializer::class,
        User::class => UserSerializer::class,
        Wall::class => WallSerializer::class,
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
