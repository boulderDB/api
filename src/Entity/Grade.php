<?php

namespace App\Entity;

use App\Helper\Behaviours;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Grade implements LocationResourceInterface, DeactivatableInterface, CacheableInterface
{
    public const RESOURCE_NAME = "Grade";

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
     * @ORM\Column(type="integer", nullable=false)
     */
    private ?int $position = null;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private bool $public = true;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $color = null;

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

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): void
    {
        $this->position = $position;
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function setPublic(bool $public): void
    {
        $this->public = $public;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): void
    {
        $this->color = $color;
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
           "/grades",
           "/boulders"
       ];
    }

    public function getBehaviours(): array
    {
        return Behaviours::getInterfaces($this);
    }
}
