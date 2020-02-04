<?php

namespace App\Components\Entity;

use App\Entity\Location;
use Doctrine\ORM\Mapping as ORM;

trait TenantTrait
{
    /**
     * @var Location
     * @ORM\ManyToOne(targetEntity="Location")
     * @ORM\JoinColumn(name="tenant_id", referencedColumnName="id")
     */
    protected $tenant;

    /**
     * @return Location
     */
    public function getTenant()
    {
        return $this->tenant;
    }

    /**
     * @param Location $tenant
     */
    public function setTenant(Location $tenant)
    {
        $this->tenant = $tenant;
    }
}