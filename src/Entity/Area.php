<?php

namespace App\Entity;

use App\Helper\Behaviours;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Area implements LocationResourceInterface, DeactivatableInterface, CacheableInterface
{
    public const RESOURCE_NAME = "Area";

    use LocationTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string")
     */
    private ?string $name = null;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Wall", fetch="LAZY", inversedBy="areas")
     * @ORM\JoinTable(name="area_walls")
     */
    private ?Collection $walls;

    /**
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private bool $active = true;

    public function __construct()
    {
        $this->walls = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getWalls(): ?Collection
    {
        return $this->walls;
    }

    public function setWalls(?Collection $walls): void
    {
        $this->walls = $walls;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function invalidates(): array
    {
        return [
            "/areas",
            "/boulders",
            "/walls"
        ];
    }

    public function getBehaviours(): array
    {
        return Behaviours::getInterfaces($this);
    }
}