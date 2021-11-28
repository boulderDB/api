<?php

namespace App\Entity;

use App\Helper\Behaviours;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Wall implements LocationResourceInterface, DeactivatableInterface, CacheableInterface
{
    public const RESOURCE_NAME = "wall";

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
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private bool $active = true;

    /**
     * @ORM\ManyToMany(targetEntity="Area", mappedBy="walls")
     */
    private ?Collection $areas;

    public function __construct()
    {
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

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getAreas(): ArrayCollection|Collection|null
    {
        return $this->areas;
    }

    public function setAreas(ArrayCollection|Collection|null $areas): void
    {
        $this->areas = $areas;
    }

    public function invalidates(): array
    {
        return [
            "/walls",
            "/boulders"
        ];
    }

    public function getBehaviours(): array
    {
        return Behaviours::getInterfaces($this);
    }
}