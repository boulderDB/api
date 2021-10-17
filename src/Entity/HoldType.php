<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="hold_color")
 * @ORM\Entity()
 */
class HoldType implements LocationResourceInterface, DeactivatableInterface, CacheableInterface
{
    public const RESOURCE_NAME = "HoldType";

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
     * @ORM\Column(type="string", nullable=true, name="icon")
     */
    private ?string $image = null;

    /**
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private bool $active = true;

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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): void
    {
        $this->image = $image;
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
            "/holdtypes",
            "/boulders"
        ];
    }

    public function getBehaviours(): array
    {
        return behaviours($this);
    }
}
