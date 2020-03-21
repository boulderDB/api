<?php

namespace App\Components\Entity;

use App\Entity\Location;

interface TenantResourceInterface
{
    public function setTenant(Location $tenant);
}