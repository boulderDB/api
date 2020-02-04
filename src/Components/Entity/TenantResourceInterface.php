<?php

namespace App\Components\Entity;

use App\Entity\Location;

interface TenantResourceInterface
{
    /**
     * @return int
     */
    public function getTenantId();

    public function setTenant(Location $tenant);
}