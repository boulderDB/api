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

    public static function isLocationRole(string $role, int $locationId): bool
    {
        return self::getLocationIdFromRoleName($role) === $locationId;
    }

    public static function getLocationIdFromRoleName(string $role): ?int
    {
        if (strpos($role, "@") === false) {
            return null;
        }

        return (int)explode("@", $role)[1];
    }

    public static function getLocationRoleName(string $role, int $locationId, bool $prefix = false): string
    {
        if ($prefix) {
            return "ROLE_{$role}@{$locationId}";
        }

        return "{$role}@{$locationId}";
    }

    public function getLocationRole(string $role): ?string
    {
        if (!$this->getLocation()) {
            return null;
        }

        return self::getLocationRoleName($role, $this->getLocation()?->getId());
    }
}