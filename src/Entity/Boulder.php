<?php

namespace App\Entity;

use App\Components\Entity\LocationResourceInterface;
use App\Components\Entity\TimestampableInterface;
use App\Components\Entity\TimestampTrait;
use App\Components\Entity\LocationTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(indexes={@ORM\Index(name="status", columns={"status"})})
 */
class Boulder implements LocationResourceInterface, TimestampableInterface
{
    public const DEFAULT_SCORE = 1000;

    use TimestampTrait;
    use LocationTrait;

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
     * @var Grade
     * @ORM\ManyToOne(targetEntity="Grade")
     * @ORM\JoinColumn(name="internal_grade_id", referencedColumnName="id")
     */
    private $internalGrade;

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
     * @ORM\ManyToMany(targetEntity="Tag", fetch="LAZY")
     * @ORM\JoinTable(name="boulder_tags",
     *     joinColumns={
     *         @ORM\JoinColumn(name="boulder_id", referencedColumnName="id"),
     *         @ORM\JoinColumn(name="tag_id", referencedColumnName="id"),
     *      })
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

        $this->points = self::DEFAULT_SCORE;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name): void
    {
        $this->name = $name;
    }

    public function getHoldStyle(): ?HoldStyle
    {
        return $this->color;
    }

    public function setHoldStyle(HoldStyle $color): void
    {
        $this->color = $color;
    }

    public function getGrade(): ?Grade
    {
        return $this->grade;
    }

    public function setGrade(Grade $grade): void
    {
        $this->grade = $grade;
    }

    public function getInternalGrade(): ?Grade
    {
        return $this->internalGrade;
    }

    public function setInternalGrade(Grade $internalGrade): void
    {
        $this->internalGrade = $internalGrade;
    }

    public function getStartWall()
    {
        return $this->startWall;
    }

    public function setStartWall($startWall): void
    {
        $this->startWall = $startWall;
    }

    public function getEndWall()
    {
        return $this->endWall;
    }

    public function setEndWall($endWall): void
    {
        $this->endWall = $endWall;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function setStatus($status): void
    {
        $this->status = $status;
    }

    public function getRemovedAt()
    {
        return $this->removedAt;
    }

    public function setRemovedAt($removedAt): void
    {
        $this->removedAt = $removedAt;
    }

    public function getSetters()
    {
        return $this->setters;
    }

    public function setSetters($setters): void
    {
        $this->setters = $setters;
    }

    public function getPoints()
    {
        return $this->points;
    }

    public function setPoints($points): void
    {
        $this->points = $points;
    }

    public function getAscents(): Collection
    {
        return $this->ascents;
    }

    public function setAscents(Collection $ascents): void
    {
        $this->ascents = $ascents;
    }

    public function clearAscents(): void
    {
        $this->ascents->clear();
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function setErrors($errors): void
    {
        $this->errors = $errors;
    }

    public function getEvents()
    {
        return $this->events;
    }

    public function setEvents($events): void
    {
        $this->events = $events;
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function setTags($tags): void
    {
        $this->tags = $tags;
    }

    public function getCurrentScore(): int
    {
        return $this->currentScore;
    }

    public function setCurrentScore(int $currentScore): void
    {
        $this->currentScore = $currentScore;
    }

    public function getAscentCount(): int
    {
        return $this->ascentCount;
    }

    public function setAscentCount(int $ascentCount): void
    {
        $this->ascentCount = $ascentCount;
    }

    public function getUserAscent(): Ascent
    {
        return $this->userAscent;
    }

    public function setUserAscent(Ascent $userAscent): void
    {
        $this->userAscent = $userAscent;
    }
}
