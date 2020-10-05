<?php

namespace App\Service;

use App\Entity\Location;

class ContextService
{
    private ?Location $location = null;

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(Location $location): void
    {
        $this->location = $location;
    }

    public static function getLocationRoleName(string $role, int $locationId): string
    {
        return "{$role}@{$locationId}";
    }

    public function getLocationRole(string $role): ?string
    {
        if (!$this->getLocation()) {
            return null;
        }

        return self::getLocationRoleName($role, $this->getLocation()->getId());
    }
}