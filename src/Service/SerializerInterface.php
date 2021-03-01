<?php

namespace App\Service;

interface SerializerInterface
{
    public const GROUP_ADMIN = "admin";
    public const GROUP_DETAIL = "detail";
    public const GROUP_INDEX = "index";

    public function serialize($class, array $groups = [], array $arguments = []): array;
}
