<?php

namespace App\Components\Entity;

use App\Entity\Location;

interface LocationResourceInterface
{
    public function setLocation(Location $tenant);
}