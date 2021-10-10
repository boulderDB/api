<?php

namespace App\Entity;

interface CacheableInterface
{
    public function invalidates(): array;
}