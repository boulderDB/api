<?php

namespace App\Service;

use App\Entity\Location;

class ContextService
{
    /**
     * @var Location
     */
    private $location;

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(Location $location): void
    {
        $this->location = $location;
    }

    public function getLocationRole(string $role): ?string
    {
        if (!$this->getLocation()) {
            return null;
        }

        return $role;

        return "{$role}@{$this->getLocation()->getId()}";
    }
}