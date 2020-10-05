<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Event implements LocationResourceInterface
{
    use TimestampTrait;
    use LocationTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string")
     */
    private ?string $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $description = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTime $dateFrom = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTime $dateTo = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $media = null;

    /**
     * @ORM\Column(type="string")
     */
    private ?string $scoringSystem = null;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private bool $publicEvent = true;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private bool $visible = true;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", fetch="LAZY", cascade={"persist"}, inversedBy="events")
     * @ORM\JoinTable(name="event_participant")
     */
    private ?Collection $participants = null;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Boulder", fetch="LAZY", inversedBy="events")
     * @ORM\JoinTable(name="event_boulder")
     */
    private ?Collection $boulders = null;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
        $this->boulders = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getDateFrom(): ?\DateTime
    {
        return $this->dateFrom;
    }

    public function setDateFrom(?\DateTime $dateFrom): void
    {
        $this->dateFrom = $dateFrom;
    }

    public function getDateTo(): ?\DateTime
    {
        return $this->dateTo;
    }

    public function setDateTo(?\DateTime $dateTo): void
    {
        $this->dateTo = $dateTo;
    }

    public function getMedia(): ?string
    {
        return $this->media;
    }

    public function setMedia(?string $media): void
    {
        $this->media = $media;
    }

    public function getScoringSystem(): ?string
    {
        return $this->scoringSystem;
    }

    public function setScoringSystem(?string $scoringSystem): void
    {
        $this->scoringSystem = $scoringSystem;
    }

    public function isPublicEvent(): bool
    {
        return $this->publicEvent;
    }

    public function setPublicEvent(bool $publicEvent): void
    {
        $this->publicEvent = $publicEvent;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): void
    {
        $this->visible = $visible;
    }

    public function getParticipants()
    {
        return $this->participants;
    }

    public function setParticipants($participants): void
    {
        $this->participants = $participants;
    }

    public function getBoulders()
    {
        return $this->boulders;
    }

    public function setBoulders($boulders): void
    {
        $this->boulders = $boulders;
    }
}