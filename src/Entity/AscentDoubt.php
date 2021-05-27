<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="boulder_doubt")
 */
class AscentDoubt implements LocationResourceInterface, TimestampableInterface, NotificationResourceInterface
{
    use TimestampTrait;
    use LocationTrait;

    const STATUS_RESOLVED = 2;
    const STATUS_READ = 1;
    const STATUS_UNREAD = 0;
    const STATUS_UNRESOLVED = -1;

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
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="recipient_id", referencedColumnName="id")
     */
    private ?User $recipient = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Boulder")
     * @ORM\JoinColumn(name="boulder_id", referencedColumnName="id")
     */
    private ?Boulder $boulder = null;

    /**
     * @ORM\Column(type="text", name="description", nullable=true)
     */
    private ?string $message = null;

    /**
     * @ORM\Column(type="integer")
     */
    private ?int $status = null;

    private ?Ascent $ascent = null;

    public function __construct()
    {
        $this->status = self::STATUS_UNREAD;
    }

    public static function getStatues(): array
    {
        return [
            "unread" => self::STATUS_UNREAD,
            "read" => self::STATUS_READ,
            "resolved" => self::STATUS_RESOLVED,
            "unresolved" => self::STATUS_UNRESOLVED
        ];
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

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): void
    {
        $this->status = $status;
    }

    public function getRecipient(): ?User
    {
        return $this->recipient;
    }

    public function setRecipient(?User $recipient): void
    {
        $this->recipient = $recipient;
    }

    public function getBoulder(): ?Boulder
    {
        return $this->boulder;
    }

    public function setBoulder(?Boulder $boulder): void
    {
        $this->boulder = $boulder;
    }

    public function getAscent(): ?Ascent
    {
        return $this->ascent;
    }

    public function setAscent(?Ascent $ascent): void
    {
        $this->ascent = $ascent;
    }

    public function getType(): string
    {
        return Notification::TYPE_DOUBT;
    }
}
