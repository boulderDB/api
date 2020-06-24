<?php

namespace App\Entity;

use App\Components\Entity\TimestampableInterface;
use App\Components\Entity\TimestampTrait;
use App\Components\Entity\LocationResourceInterface;
use App\Components\Entity\LocationTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="boulder_doubt")
 * @ORM\HasLifecycleCallbacks()
 */
class AscentDoubt implements LocationResourceInterface, TimestampableInterface
{
    use TimestampTrait;
    use LocationTrait;

    const STATUS_UNREAD = 0;
    const STATUS_READ = 1;
    const STATUS_RESOLVED = 2;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id")
     */
    private $author;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="recipient_id", referencedColumnName="id")
     */
    private $recipient;

    /**
     * @var Boulder
     * @ORM\ManyToOne(targetEntity="Boulder")
     * @ORM\JoinColumn(name="boulder_id", referencedColumnName="id")
     */
    private $boulder;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $status;

    /**
     * AscentDoubt constructor.
     */
    public function __construct()
    {
        $this->status = self::STATUS_UNREAD;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param User $author
     */
    public function setAuthor(User $author)
    {
        $this->author = $author;
    }

    /**
     * @return User
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @param User $recipient
     */
    public function setRecipient(User $recipient)
    {
        $this->recipient = $recipient;
    }

    /**
     * @return Boulder
     */
    public function getBoulder()
    {
        return $this->boulder;
    }

    /**
     * @param Boulder $boulder
     */
    public function setBoulder(Boulder $boulder)
    {
        $this->boulder = $boulder;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus(int $status = self::STATUS_UNREAD)
    {
        $this->status = $status;
    }

    public function setMessage(string $message): void
    {
        $this->description = $message;
    }

    public function getMessage(): ?string
    {
        return $this->description;
    }
}
