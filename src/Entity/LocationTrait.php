<?php

namespace App\Entity;

use App\Entity\Location;
use Doctrine\ORM\Mapping as ORM;

trait LocationTrait
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Location")
     * @ORM\JoinColumn(name="tenant_id", referencedColumnName="id")
     */
    protected ?Location $location;

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(Location $location): void
    {
        $this->location = $location;
    }
}