<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class BoulderError implements LocationResourceInterface, TimestampableInterface, NotificationResourceInterface
{
    use TimestampTrait;
    use LocationTrait;

    const STATUS_RESOLVED = "resolved";
    const STATUS_UNRESOLVED = "unresolved";

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
     * @ORM\ManyToOne(targetEntity="Boulder", inversedBy="errors")
     * @ORM\JoinColumn(name="boulder_id", referencedColumnName="id")
     */
    private ?Boulder $boulder = null;

    /**
     * @ORM\Column(type="text", name="description", nullable=true)
     */
    private ?string $message = null;

    /**
     * @ORM\Column(type="string")
     */
    private ?string $status = self::STATUS_UNRESOLVED;

    public static function getStatuses(): array
    {
        return [self::STATUS_RESOLVED, self::STATUS_UNRESOLVED];
    }

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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    public function getType(): string
    {
       return Notifications::TYPE_ERRORS;
    }
}
