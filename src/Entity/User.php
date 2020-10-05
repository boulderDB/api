<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="users")
 * @ORM\Entity()
 */
class User implements UserInterface
{
    public const USER = 'USER';
    public const SETTER = 'SETTER';
    public const ADMIN = 'ADMIN';

    public const ROLE_USER = 'ROLE_' . self::USER;
    public const ROLE_SETTER = 'ROLE_' . self::SETTER;
    public const ROLE_ADMIN = 'ROLE_' . self::ADMIN;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=64, unique=true)
     */
    private ?string $username = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $firstName = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $lastName = null;

    /**
     * @ORM\Column(type="string", length=64, unique=true)
     */
    private ?string $email = null;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private ?string $password = null;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $active = true;

    /**
     * @ORM\Column(type="array")
     */
    private array $roles = [];

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $visible = true;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $media = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $lastVisitedLocation = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTime $lastActivity = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Ascent", mappedBy="user", fetch="LAZY")
     */
    private ?Collection $ascents = null;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Boulder", mappedBy="setters", fetch="LAZY")
     */
    private ?Collection $boulders = null;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Event", mappedBy="participants", fetch="LAZY")
     */
    private ?Collection $events = null;

    private ?string $plainPassword = null;

    public function __construct()
    {
        $this->ascents = new ArrayCollection();
        $this->boulders = new ArrayCollection();
        $this->events = new ArrayCollection();

        $this->visible = true;
        $this->active = true;
        $this->roles = [self::ROLE_USER];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): void
    {
        $this->username = $username;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): void
    {
        $this->visible = $visible;
    }

    public function getMedia(): ?string
    {
        return $this->media;
    }

    public function setMedia(?string $media): void
    {
        $this->media = $media;
    }

    public function getLastVisitedLocation(): ?int
    {
        return $this->lastVisitedLocation;
    }

    public function setLastVisitedLocation(?int $lastVisitedLocation): void
    {
        $this->lastVisitedLocation = $lastVisitedLocation;
    }

    public function getLastActivity(): ?\DateTime
    {
        return $this->lastActivity;
    }

    public function setLastActivity(?\DateTime $lastActivity): void
    {
        $this->lastActivity = $lastActivity;
    }

    public function getAscents()
    {
        return $this->ascents;
    }

    public function setAscents($ascents): void
    {
        $this->ascents = $ascents;
    }

    public function getBoulders()
    {
        return $this->boulders;
    }

    public function setBoulders($boulders): void
    {
        $this->boulders = $boulders;
    }

    public function getEvents()
    {
        return $this->events;
    }

    public function setEvents($events): void
    {
        $this->events = $events;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }

    public function getSalt()
    {
        return null;
    }

    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }
}
