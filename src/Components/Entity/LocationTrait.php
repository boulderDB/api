<?php

namespace App\Components\Entity;

use App\Entity\Location;
use Doctrine\ORM\Mapping as ORM;

trait LocationTrait
{
    /**
     * @var Location
     * @ORM\ManyToOne(targetEntity="Location")
     * @ORM\JoinColumn(name="tenant_id", referencedColumnName="id")
     */
    protected $location;

    /**
     * @return Location
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param Location $location
     */
    public function setLocation(Location $location)
    {
        $this->location = $location;
    }
}