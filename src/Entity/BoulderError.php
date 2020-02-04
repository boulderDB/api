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

    /**
     * BoulderError constructor.
     */
    public function __construct()
    {
        $this->status = self::STATUS_UNRESOLVED;
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

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status)
    {
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getTenantId()
    {
        return $this->tenant->getId();
    }
}
