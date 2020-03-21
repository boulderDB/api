<?php

namespace App\Entity;

use App\Components\Entity\TimestampTrait;
use App\Components\Entity\TenantResourceInterface;
use App\Components\Entity\TenantTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BoulderErrorRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class BoulderError implements TenantResourceInterface
{
    use TimestampTrait;
    use TenantTrait;

    const STATUS_RESOLVED = 'resolved';
    const STATUS_UNRESOLVED = 'unresolved';

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
     * @var Boulder
     * @ORM\ManyToOne(targetEntity="Boulder", inversedBy="errors")
     * @ORM\JoinColumn(name="boulder_id", referencedColumnName="id")
     */
    private $boulder;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $status;

    public function __construct()
    {
        $this->status = self::STATUS_UNRESOLVED;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(User $author): void
    {
        $this->author = $author;
    }

    public function getBoulder(): ?Boulder
    {
        return $this->boulder;
    }

    public function setBoulder(Boulder $boulder): void
    {
        $this->boulder = $boulder;
    }

    public function getMessage(): ?string
    {
        return $this->description;
    }

    public function setMessage(string $message): void
    {
        $this->description = $message;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus(string $status)
    {
        $this->status = $status;
    }
}
