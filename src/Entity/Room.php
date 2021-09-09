<?php

namespace App\Entity;

use App\Entity\LocationResourceInterface;
use App\Entity\LocationTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="room")
 * @ORM\Entity()
 */
class Room implements LocationResourceInterface
{
    public const RESOURCE_NAME = "Room";

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
    private ?string $instructions = null;

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

    public function getInstructions(): ?string
    {
        return $this->instructions;
    }

    public function setInstructions(?string $instructions): void
    {
        $this->instructions = $instructions;
    }
}
