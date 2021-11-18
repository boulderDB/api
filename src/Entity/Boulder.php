<?php

namespace App\Entity;

use App\Helper\Behaviours;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(indexes={@ORM\Index(name="status", columns={"status"})})
 */
class Boulder implements LocationResourceInterface, TimestampableInterface, CacheableInterface
{
    use TimestampTrait;
    use LocationTrait;

    public const RESOURCE_NAME = "boulder";
    public const DEFAULT_SCORE = 1000;
    public const STATUS_ACTIVE = "active";
    public const STATUS_INACTIVE = "inactive";

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
     * @ORM\ManyToOne(targetEntity="App\Entity\Wall", fetch="EAGER")
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
     * @ORM\OneToMany(targetEntity="App\Entity\Ascent", mappedBy="boulder", fetch="LAZY")
     */
    private ?Collection $ascents = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\BoulderComment", mappedBy="boulder", fetch="LAZY")
     */
    private ?Collection $comments = null;

    /**
     * @ORM\ManyToMany(targetEntity="BoulderTag", fetch="LAZY")
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

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\BoulderRating", mappedBy="boulder", fetch="LAZY")
     */
    private ?Collection $ratings = null;

    private ?Ascent $userAscent = null;

    private ?int $currentPoints = null;

    public function __construct()
    {
        $this->setters = new ArrayCollection();
        $this->ascents = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->ratings = new ArrayCollection();
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
        return $this->endWall ?: $this->startWall;
    }

    public function setEndWall(?Wall $endWall): void
    {
        $this->endWall = $endWall;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function setStatus(?string $status): void
    {
        if (!$status) {
            return;
        }

        if ($this->status === Boulder::STATUS_ACTIVE && $status == Boulder::STATUS_INACTIVE) {
            $this->removedAt = new \DateTime();
        }

        $this->status = $status;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function setPoints(int $points): void
    {
        $this->currentPoints = $points;
    }

    public function getCurrentPoints(): int
    {
        return $this->currentPoints ? $this->currentPoints : $this->points;
    }

    public function setCurrentPoints(int $points): void
    {
        $this->currentPoints = $points;
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

    public function setUserAscent(int $userId): void
    {
        $match = $this->ascents->filter(function ($ascent) use ($userId) {
            /**
             * @var Ascent $ascent
             */
            return $ascent->getUser()->getId() === $userId;
        })->first();

        $this->userAscent = $match ?: null;
    }

    public function getUserAscent(): ?Ascent
    {
        return $this->userAscent;
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

    public function getComments()
    {
        return $this->comments;
    }

    public function setComments($comments): void
    {
        $this->comments = $comments;
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function setTags($tags): void
    {
        $this->tags = $tags;
    }

    public function getRatings()
    {
        return $this->ratings;
    }

    public function setRatings($ratings): void
    {
        $this->ratings = $ratings;
    }

    public function invalidates(): array
    {
        return [
            "/boulders",
            "/rankings"
        ];
    }

    public function getBehaviours(): array
    {
        return Behaviours::getInterfaces($this);
    }
}
