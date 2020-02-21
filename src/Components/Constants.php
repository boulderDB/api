<?php

namespace App\Components;

interface Constants
{
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'removed';

    const BOULDER_DEFAULT_SCORE = 1000;

    const ASCENT_TOPPED = 'top';
    const ASCENT_FLASHED = 'flash';
    const ASCENT_RESIGNED = 'resignation';

    const ASCENT_TYPES = [
        self::ASCENT_FLASHED,
        self::ASCENT_TOPPED,
        self::ASCENT_RESIGNED
    ];

    const SCORED_ASCENT_TYPES = [
        self::ASCENT_FLASHED,
        self::ASCENT_TOPPED,
    ];

    const ROLE_SETTER = 'ROLE_SETTER';
    const ROLE_USER = 'ROLE_USER';
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_SUPER_USER = 'ROLE_SUPER_ADMIN';

    const ROLES = [
        self::ROLE_SETTER,
        self::ROLE_USER,
        self::ROLE_ADMIN,
        self::ROLE_SUPER_USER
    ];
}