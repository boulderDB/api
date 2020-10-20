<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="tag")
 */
class Tag implements LocationResourceInterface
{
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
    private ?string  $emoji = null;

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
}
