<?php

use App\Entity\CacheableInterface;
use App\Entity\DeactivatableInterface;
use App\Entity\LocationResourceInterface;
use App\Entity\NotificationResourceInterface;
use App\Entity\TimestampableInterface;
use App\Entity\UserResourceInterface;

function behaviours($class): array
{
    $interfaces = [];

    if ($class instanceof UserResourceInterface) {
        $interfaces[] = "UserResource";
    }

    if ($class instanceof LocationResourceInterface) {
        $interfaces[] = "LocationResource";
    }

    if ($class instanceof CacheableInterface) {
        $interfaces[] = "Cacheable";
    }

    if ($class instanceof DeactivatableInterface) {
        $interfaces[] = "Deactivatable";
    }

    if ($class instanceof NotificationResourceInterface) {
        $interfaces[] = "NotificationResource";
    }

    if ($class instanceof TimestampableInterface) {
        $interfaces[] = "Timestampable";
    }

    return $interfaces;
}