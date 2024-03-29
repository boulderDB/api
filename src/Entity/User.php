<?php

namespace App\Entity;

use App\Helper\Behaviours;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const RESOURCE_NAME = "user";

    public const USER = "USER";
    public const SETTER = "SETTER";
    public const ADMIN = "ADMIN";
    public const COUNTER = "COUNTER";

    public const ROLE_USER = "ROLE_" . self::USER;
    public const ROLE_SETTER = "ROLE_" . self::SETTER;
    public const ROLE_ADMIN = "ROLE_" . self::ADMIN;
    public const ROLE_COUNTER = "ROLE_" . self::COUNTER;

    public const ROLES = [
        self::USER,
        self::SETTER,
        self::ADMIN,
        self::COUNTER
    ];

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
     * @ORM\Column(type="string")
     */
    private ?string $gender = null;

    /**
     * @ORM\Column(type="string", length=64, unique=true)
     */
    private ?string $email = null;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private ?string $password = null;

    /**
     * @ORM\Column(type="boolean", options={"default": true})
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
     * @ORM\Column(name="media", type="string", nullable=true)
     */
    private ?string $image = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Location")
     * @ORM\JoinColumn(name="last_visited_location", referencedColumnName="id")
     */
    private ?Location $lastVisitedLocation = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $lastActivity = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTime $lastLogin = null;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Notification", fetch="LAZY")
     * @ORM\JoinTable(name="user_notifications",
     *     joinColumns={
     *         @ORM\JoinColumn(name="notification_id", referencedColumnName="id")
     *      },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="user_id", referencedColumnName="id"),
     *     }
     * )
     */
    private ?Collection $notifications = null;

    private ?string $plainPassword = null;

    public function __construct()
    {
        $this->visible = true;
        $this->active = true;
        $this->roles = [self::ROLE_USER];
        $this->notifications = new ArrayCollection();
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

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
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
        $roles = $this->roles;
        $roles = array_filter($roles, function (string $role) {
            return $role !== "ROLE_USER";
        });
        $roles = array_unique($roles);

        return array_values($roles);
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function addRole(string $role): void
    {
        $this->roles[] = $role;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): void
    {
        $this->visible = $visible;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): void
    {
        $this->image = $image;
    }

    public function getLastVisitedLocation(): ?Location
    {
        return $this->lastVisitedLocation;
    }

    public function setLastVisitedLocation(?Location $lastVisitedLocation): void
    {
        $this->lastVisitedLocation = $lastVisitedLocation;
    }

    public function getLastActivity(): ?\DateTimeInterface
    {
        return $this->lastActivity;
    }

    public function setLastActivity(?\DateTime $lastActivity): void
    {
        $this->lastActivity = $lastActivity;
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

    public function getLastLogin(): ?\DateTime
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?\DateTime $lastLogin): void
    {
        $this->lastLogin = $lastLogin;
    }

    public function getNotifications(): ?Collection
    {
        return $this->notifications;
    }

    public function setNotifications(?Collection $notifications): void
    {
        $this->notifications = $notifications;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function getBehaviours(): array
    {
        return Behaviours::getInterfaces($this);
    }
}
