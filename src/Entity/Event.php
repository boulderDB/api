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
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private ?bool $visible = true;

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
    private ?\DateTime $endDate = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $ranking;

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

    public function getVisible(): ?bool
    {
        return $this->visible;
    }

    public function setVisible(?bool $visible): void
    {
        $this->visible = $visible;
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

    public function isParticipant(User $user = null): bool
    {
        if (!$user) {
            return false;
        }

        return (bool)$this->participants->filter(function ($participant) use ($user) {
            /**
             * @var \App\Entity\User $participant
             */
            return $participant->getId() === $user->getId();
        })?->first();
    }

    public function findBoulder(int $id): ?Boulder
    {
        $match = $this->boulders->filter(function ($boulder) use ($id) {
            /**
             * @var \App\Entity\Boulder $boulder
             */
            return $boulder->getId() === $id;
        })?->first();

        return $match ? $match: null;
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

    public function getEndDate(): ?\DateTime
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTime $endDate): void
    {
        $this->endDate = $endDate;
    }

    public function getState(): string
    {
        $current = new \DateTime("now", new \DateTimeZone("Europe/Berlin"));

        if ($current > $this->getStartDate() && $current < $this->getEndDate() && $this->getVisible()) {
            return "active";
        }

        if ($current > $this->getEndDate()) {
            return "ended";
        }

        if ($current < $this->getStartDate()) {
            return "upcoming";
        }

        throw new \LogicException("Unexpected event state");
    }

    public function hasEnded(): bool
    {
        $current = new \DateTime("now", new \DateTimeZone("Europe/Berlin"));

        return $current > $this->getEndDate();
    }

    public function isActive(): bool
    {
        $current = new \DateTime("now", new \DateTimeZone("Europe/Berlin"));

        return $current > $this->getStartDate() && $current < $this->getEndDate() && $this->getVisible();
    }

    public function getRanking(): ?string
    {
        return $this->ranking;
    }

    public function setRanking(?string $ranking): void
    {
        $this->ranking = $ranking;
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