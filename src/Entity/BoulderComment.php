<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class BoulderComment implements LocationResourceInterface, TimestampableInterface, NotificationResourceInterface
{
    public const RESOURCE_NAME = "BoulderComment";

    use TimestampTrait;
    use LocationTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id")
     */
    private ?User $author = null;

    /**
     * @ORM\ManyToOne(targetEntity="Boulder")
     * @ORM\JoinColumn(name="boulder_id", referencedColumnName="id")
     */
    private ?Boulder $boulder = null;

    /**
     * @ORM\Column(type="text", name="description", nullable=true)
     */
    private ?string $message = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): void
    {
        $this->author = $author;
    }

    public function getBoulder(): ?Boulder
    {
        return $this->boulder;
    }

    public function setBoulder(?Boulder $boulder): void
    {
        $this->boulder = $boulder;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }

    public function getType(): string
    {
        return Notification::TYPE_COMMENT;
    }
}
