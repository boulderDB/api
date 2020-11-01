<?php

namespace App\Entity;

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
     * @ORM\ManyToOne(targetEntity="App\Entity\HoldType", fetch="EAGER")
     * @ORM\JoinColumn(name="color_id", referencedColumnName="id", nullable=true)
     */
    private ?HoldType $holdType = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Grade", fetch="EAGER")
     * @ORM\JoinColumn(name="grade_id", referencedColumnName="id")
     */
    private ?Grade $grade = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Grade", fetch="EAGER")
     * @ORM\JoinColumn(name="internal_grade_id", referencedColumnName="id")
     */
    private ?Grade $internalGrade = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Wall", inversedBy="boulders", fetch="EAGER")
     * @ORM\JoinColumn(name="start_wall_id", referencedColumnName="id")
     */
    private ?Wall $startWall = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Wall", fetch="EAGER")
     * @ORM\JoinColumn(name="end_wall_id", referencedColumnName="id", nullable=true)
     */
    private ?Wall $endWall = null;

    /**
     * @ORM\Column(type="string")
     */
    private string $status = self::STATUS_ACTIVE;

    /**
     * @ORM\Column(type="integer")
     */
    private int $points = self::DEFAULT_SCORE;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTime $removedAt = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Ascent", mappedBy="boulder", fetch="LAZY", cascade={"remove"})
     */
    private ?Collection $ascents = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\BoulderError", mappedBy="boulder", fetch="LAZY")
     */
    private ?Collection $errors = null;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Tag", fetch="LAZY", inversedBy="boulders")
     * @ORM\JoinTable(name="boulder_tags",
     *     joinColumns={
     *         @ORM\JoinColumn(name="boulder_id", referencedColumnName="id")
     *      },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="tag_id", referencedColumnName="id"),
     *     }
     * )
     */
    private ?Collection $tags = null;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Setter", fetch="LAZY", inversedBy="boulders")
     * @ORM\JoinTable(name="boulder_setters_v2",
     *     joinColumns={
     *         @ORM\JoinColumn(name="boulder_id", referencedColumnName="id")
     *      },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="setter_id", referencedColumnName="id"),
     *     }
     * )
     */
    private ?Collection $setters = null;

    public function __construct()
    {
        $this->setters = new ArrayCollection();
        $this->ascents = new ArrayCollection();
        $this->errors = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public static function getStatuses(): array
    {
        return [self::STATUS_ACTIVE, self::STATUS_INACTIVE];
    }

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

    public function getHoldType(): ?HoldType
    {
        return $this->holdType;
    }

    public function setHoldType(?HoldType $holdType): void
    {
        $this->holdType = $holdType;
    }

    public function getGrade(): ?Grade
    {
        return $this->grade;
    }

    public function setGrade(?Grade $grade): void
    {
        $this->grade = $grade;
    }

    public function getInternalGrade(): ?Grade
    {
        return $this->internalGrade;
    }

    public function setInternalGrade(?Grade $internalGrade): void
    {
        $this->internalGrade = $internalGrade;
    }

    public function getStartWall(): ?Wall
    {
        return $this->startWall;
    }

    public function setStartWall(?Wall $startWall): void
    {
        $this->startWall = $startWall;
    }

    public function getEndWall(): ?Wall
    {
        return $this->endWall;
    }

    public function setEndWall(?Wall $endWall): void
    {
        $this->endWall = $endWall;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function setPoints(int $points): void
    {
        $this->points = $points;
    }

    public function getRemovedAt(): ?\DateTime
    {
        return $this->removedAt;
    }

    public function setRemovedAt(?\DateTime $removedAt): void
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

    public function getAscents()
    {
        return $this->ascents->filter(function ($ascent) {

            /**
             * @var Ascent $ascent
             */
            return $ascent->getUser()->isVisible();
        });
    }

    public function setAscents($ascents): void
    {
        $this->ascents = $ascents;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function setErrors($errors): void
    {
        $this->errors = $errors;
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function setTags($tags): void
    {
        $this->tags = $tags;
    }
}
