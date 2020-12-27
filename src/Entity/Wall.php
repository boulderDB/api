<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Wall implements LocationResourceInterface
{
    use LocationTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    private ?string $name = null;

    /**
     * @ORM\Column(name="description", type="text")
     */
    private ?string $description = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $media = null;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Area", mappedBy="walls", fetch="LAZY")
     */
    private ?Collection $areas = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Boulder", mappedBy="startWall", fetch="LAZY")
     */
    private ?Collection $boulders = null;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private bool $active = true;

    public function __construct()
    {
        $this->boulders = new ArrayCollection();
        $this->areas = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getMedia(): ?string
    {
        return $this->media;
    }

    public function setMedia(?string $media): void
    {
        $this->media = $media;
    }

    public function getAreas(): ?Collection
    {
        return $this->areas;
    }

    public function setAreas(Collection $areas): void
    {
        $this->areas = $areas;
    }

    public function getBoulders(): ?Collection
    {
        return $this->boulders;
    }

    public function setBoulders(Collection $boulders): void
    {
        $this->boulders = $boulders;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }
}