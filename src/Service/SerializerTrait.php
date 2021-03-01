<?php

namespace App\Service;


trait SerializerTrait
{
    public static function matchesGroup(string $group): bool
    {
        return in_array($group, [
            SerializerInterface::GROUP_ADMIN,
            SerializerInterface::GROUP_DETAIL,
            SerializerInterface::GROUP_INDEX,
        ]);
    }
}