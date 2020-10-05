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
     * @ORM\ManyToOne(targetEntity="App\Entity\HoldType")
     * @ORM\JoinColumn(name="color_id", referencedColumnName="id", nullable=true)
     */
    private ?HoldType $color = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Grade")
     * @ORM\JoinColumn(name="grade_id", referencedColumnName="id")
     */
    private ?Grade $grade = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Grade")
     * @ORM\JoinColumn(name="internal_grade_id", referencedColumnName="id")
     */
    private ?Grade $internalGrade = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Wall", inversedBy="boulders")
     * @ORM\JoinColumn(name="start_wall_id", referencedColumnName="id")
     */
    private ?Wall $startWall = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Wall")
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
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="boulders", fetch="LAZY")
     * @ORM\JoinTable(name="boulder_setters")
     */
    private ?Collection $setters = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Ascent", mappedBy="boulder", fetch="LAZY", cascade={"remove"})
     */
    private ?Collection $ascents = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\BoulderError", mappedBy="boulder", fetch="LAZY")
     */
    private ?Collection $errors = null;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\BoulderTag", fetch="LAZY")
     * @ORM\JoinTable(name="boulder_tags",
     *     joinColumns={
     *         @ORM\JoinColumn(name="boulder_id", referencedColumnName="id"),
     *         @ORM\JoinColumn(name="tag_id", referencedColumnName="id"),
     *      })
     */
    private ?Collection $tags = null;

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
}
