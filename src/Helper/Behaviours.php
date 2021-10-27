<?php

namespace App\Helper;

use App\Entity\CacheableInterface;
use App\Entity\DeactivatableInterface;
use App\Entity\LocationResourceInterface;
use App\Entity\NotificationResourceInterface;
use App\Entity\TimestampableInterface;
use App\Entity\UserResourceInterface;

class Behaviours
{
    public static function getInterfaces($class): array
    {
        $interfaces = [];

        if ($class instanceof UserResourceInterface) {
            $interfaces[] = "userResource";
        }

        if ($class instanceof LocationResourceInterface) {
            $interfaces[] = "locationResource";
        }

        if ($class instanceof CacheableInterface) {
            $interfaces[] = "cacheable";
        }

        if ($class instanceof DeactivatableInterface) {
            $interfaces[] = "deactivatable";
        }

        if ($class instanceof NotificationResourceInterface) {
            $interfaces[] = "notificationResource";
        }

        if ($class instanceof TimestampableInterface) {
            $interfaces[] = "timestampable";
        }

        return $interfaces;
    }
}