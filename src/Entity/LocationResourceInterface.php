<?php

namespace App\Entity;

use App\Entity\Location;

interface LocationResourceInterface
{
    public function setLocation(Location $tenant);
}