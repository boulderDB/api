<?php

namespace App\Entity;

use App\Helper\Behaviours;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Event implements LocationResourceInterface, CacheableInterface
{
    public const RESOURCE_NAME = "event";

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
    private ?string $name = null;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Boulder", fetch="LAZY", inversedBy="events")
     * @ORM\JoinTable(name="event_boulder")
     */
    private ?Collection $boulders;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", fetch="LAZY", inversedBy="events")
     * @ORM\JoinTable(name="event_user")
     */
    private ?Collection $participants;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private bool $public = false;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTime $startDate = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTime $endData = null;

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

    public function getBoulders(): ?Collection
    {
        return $this->boulders;
    }

    public function setBoulders(?Collection $boulders): void
    {
        $this->boulders = $boulders;
    }

    public function getParticipants(): ?Collection
    {
        return $this->participants;
    }

    public function setParticipants(?Collection $participants): void
    {
        $this->participants = $participants;
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function setPublic(bool $public): void
    {
        $this->public = $public;
    }

    public function getStartDate(): ?\DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTime $startDate): void
    {
        $this->startDate = $startDate;
    }

    public function getEndData(): ?\DateTime
    {
        return $this->endData;
    }

    public function setEndData(?\DateTime $endData): void
    {
        $this->endData = $endData;
    }

    public function invalidates(): array
    {
        return [
            "/events",
        ];
    }

    public function getBehaviours(): array
    {
        return Behaviours::getInterfaces($this);
    }
}