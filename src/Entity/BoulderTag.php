<?php

namespace App\Entity;

use App\Helper\Behaviours;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="tag")
 */
class BoulderTag implements LocationResourceInterface, DeactivatableInterface, CacheableInterface
{
    public const RESOURCE_NAME = "boulderTag";

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
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $emoji = null;

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

    public function getEmoji(): ?string
    {
        return $this->emoji;
    }

    public function setEmoji(?string $emoji): void
    {
        $this->emoji = $emoji;
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
           "/boulders",
           "/boulder-tags"
       ];
    }

    public function getBehaviours(): array
    {
        return Behaviours::getInterfaces($this);
    }
}
