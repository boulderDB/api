<?php

namespace App\Entity;

interface LocationResourceInterface
{
    public function setLocation(Location $tenant);

    public function getLocation(): ?Location;
}