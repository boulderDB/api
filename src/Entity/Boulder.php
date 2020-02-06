<?php

namespace App\Entity;

use App\Components\Constants;
use App\Components\Entity\TimestampTrait;
use App\Components\Entity\TenantTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(indexes={@ORM\Index(name="status", columns={"status"})})
 */
class Boulder
{
    use TimestampTrait;
    use TenantTrait;

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const NEW_BOULDERS_DATE_MODIFIER = '-14days';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="HoldStyle")
     * @ORM\JoinColumn(name="color_id", referencedColumnName="id", nullable=true)
     */
    private $color;

    /**
     * @ORM\ManyToOne(targetEntity="Grade")
     * @ORM\JoinColumn(name="grade_id", referencedColumnName="id")
     */
    private $grade;

    /**
     * @ORM\ManyToOne(targetEntity="Wall", inversedBy="boulders")
     * @ORM\JoinColumn(name="start_wall_id", referencedColumnName="id")
     */
    private $startWall;

    /**
     * @ORM\ManyToOne(targetEntity="Wall")
     * @ORM\JoinColumn(name="end_wall_id", referencedColumnName="id", nullable=true)
     */
    private $endWall;

    /**
     * @ORM\Column(type="string")
     */
    private $status;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $removedAt;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="boulders", fetch="LAZY")
     * @ORM\JoinTable(name="boulder_setters")
     */
    private $setters;

    /**
     * @ORM\Column(type="integer")
     */
    private $points;

    /**
     * @ORM\OneToMany(targetEntity="Ascent", mappedBy="boulder", fetch="LAZY", cascade={"remove"})
     */
    private $ascents;

    /**
     * @ORM\OneToMany(targetEntity="BoulderError", mappedBy="boulder", fetch="LAZY")
     */
    private $errors;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Event", mappedBy="boulders", fetch="LAZY")
     */
    private $events;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Tag", fetch="LAZY")
     * @ORM\JoinTable(name="boulder_tags")
     */
    private $tags;

    /**
     * @var int
     */
    private $currentScore;

    /**
     * @var int
     */
    private $ascentCount;

    /**
     * @var Ascent
     */
    private $userAscent;

    public function __construct()
    {
        $this->setters = new ArrayCollection();
        $this->ascents = new ArrayCollection();
        $this->errors = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->tags = new ArrayCollection();

        $this->points = Constants::BOULDER_DEFAULT_SCORE;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param mixed $color
     */
    public function setColor($color): void
    {
        $this->color = $color;
    }

    /**
     * @return mixed
     */
    public function getGrade()
    {
        return $this->grade;
    }

    /**
     * @param mixed $grade
     */
    public function setGrade($grade): void
    {
        $this->grade = $grade;
    }

    /**
     * @return mixed
     */
    public function getStartWall()
    {
        return $this->startWall;
    }

    /**
     * @param mixed $startWall
     */
    public function setStartWall($startWall): void
    {
        $this->startWall = $startWall;
    }

    /**
     * @return mixed
     */
    public function getEndWall()
    {
        return $this->endWall;
    }

    /**
     * @param mixed $endWall
     */
    public function setEndWall($endWall): void
    {
        $this->endWall = $endWall;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getRemovedAt()
    {
        return $this->removedAt;
    }

    /**
     * @param mixed $removedAt
     */
    public function setRemovedAt($removedAt): void
    {
        $this->removedAt = $removedAt;
    }

    /**
     * @return mixed
     */
    public function getSetters()
    {
        return $this->setters;
    }

    /**
     * @param mixed $setters
     */
    public function setSetters($setters): void
    {
        $this->setters = $setters;
    }

    /**
     * @return mixed
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * @param mixed $points
     */
    public function setPoints($points): void
    {
        $this->points = $points;
    }

    /**
     * @return mixed
     */
    public function getAscents(): Collection
    {
        return $this->ascents;
    }

    /**
     * @param mixed $ascents
     */
    public function setAscents($ascents): void
    {
        $this->ascents = $ascents;
    }

    /**
     * @return mixed
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param mixed $errors
     */
    public function setErrors($errors): void
    {
        $this->errors = $errors;
    }

    /**
     * @return mixed
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * @param mixed $events
     */
    public function setEvents($events): void
    {
        $this->events = $events;
    }

    /**
     * @return mixed
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param mixed $tags
     */
    public function setTags($tags): void
    {
        $this->tags = $tags;
    }

    /**
     * @return int
     */
    public function getCurrentScore(): int
    {
        return $this->currentScore;
    }

    /**
     * @param int $currentScore
     */
    public function setCurrentScore(int $currentScore): void
    {
        $this->currentScore = $currentScore;
    }

    /**
     * @return int
     */
    public function getAscentCount(): int
    {
        return $this->ascentCount;
    }

    /**
     * @param int $ascentCount
     */
    public function setAscentCount(int $ascentCount): void
    {
        $this->ascentCount = $ascentCount;
    }

    /**
     * @return Ascent
     */
    public function getUserAscent(): Ascent
    {
        return $this->userAscent;
    }

    /**
     * @param Ascent $userAscent
     */
    public function setUserAscent(Ascent $userAscent): void
    {
        $this->userAscent = $userAscent;
    }
}
