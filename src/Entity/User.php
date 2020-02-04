<?php

namespace App\Entity;

use App\Components\Entity\TimestampTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="users")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 */
class User implements UserInterface, \Serializable, EquatableInterface
{
    use TimestampTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=64, unique=true)
     */
    private $username;

    /**
     * @var string
     * @ORM\Column(type="string", length=64, unique=true)
     */
    private $email;

    /**
     * @var string
     * @ORM\Column(type="string", length=64)
     */
    private $password;

    /**
     * @var string
     */
    private $plainPassword;

    /**
     * @var string
     * @ORM\Column(type="string", length=64)
     */
    private $gender;

    /**
     * @var \DateTime
     * @ORM\Column(type="date", nullable=true)
     */
    private $birthday;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastLogin;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastActivity;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $signature;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $armSpan;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $height;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    private $active;

    /**
     * @var array
     * @ORM\Column(type="array")
     */
    private $roles;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    private $visible;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Ascent", mappedBy="user", fetch="LAZY")
     */
    private $ascents;

    /**
     * Set boulders
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Entity\Boulder", mappedBy="setters", fetch="LAZY")
     */
    private $boulders;

    /**
     * @var UploadedFile
     * @ORM\Column(type="string", nullable=true)
     */
    private $media;

    /**
     * @var Event[]
     * @ORM\ManyToMany(targetEntity="App\Entity\Event", mappedBy="participants", fetch="LAZY")
     */
    private $events;

    /**
     * @var Location[]
     * @ORM\ManyToMany(targetEntity="Location", fetch="LAZY")
     * @ORM\JoinTable(name="user_tenants")
     */
    private $tenants;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    private $lastVisitedLocation;

    /**
     * @var int
     * @orm\column(type="integer", nullable=true)
     */
    private $weight;

    /**
     * @var array
     * @ORM\Column(type="array", nullable=true)
     */
    private $migrations = [];

    public function __construct()
    {
        $this->ascents = new ArrayCollection();
        $this->boulders = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->tenants = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @param string $plainPassword
     */
    public function setPlainPassword(string $plainPassword)
    {
        $this->plainPassword = $plainPassword;
    }

    /**
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param string $gender
     */
    public function setGender(string $gender)
    {
        $this->gender = $gender;
    }

    /**
     * @return \DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * @param \DateTime $birthday
     */
    public function setBirthday(\DateTime $birthday = null)
    {
        $this->birthday = $birthday;
    }

    /**
     * @return \DateTime
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * @param \DateTime $lastLogin
     */
    public function setLastLogin(\DateTime $lastLogin = null)
    {
        $this->lastLogin = $lastLogin;
    }

    /**
     * @return \DateTime
     */
    public function getLastActivity()
    {
        return $this->lastActivity;
    }

    /**
     * @param \DateTime $lastActivity
     */
    public function setLastActivity(\DateTime $lastActivity = null)
    {
        $this->lastActivity = $lastActivity;
    }

    /**
     * @return string
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * @param string $signature
     */
    public function setSignature(string $signature)
    {
        $this->signature = $signature;
    }

    /**
     * @return int
     */
    public function getArmSpan()
    {
        return $this->armSpan;
    }

    /**
     * @param int $armSpan
     */
    public function setArmSpan(int $armSpan = null)
    {
        $this->armSpan = $armSpan;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param int $height
     */
    public function setHeight(int $height = null)
    {
        $this->height = $height;
    }

    /**
     * @return int
     */
    public function getApeIndex()
    {
        return $this->armSpan - $this->height;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active)
    {
        $this->active = $active;
    }

    /**
     * @param string $role
     * @return bool
     */
    public function hasRole(string $role)
    {
        return in_array($role, $this->roles);
    }

    /**
     * @inheritdoc
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;
    }

    /**
     * @param string $role
     */
    public function addRole(string $role)
    {
        $this->roles[] = $role;
    }

    /**
     * @inheritdoc
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return serialize([
            $this->id,
            $this->username,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            ) = unserialize($serialized);
    }

    /**
     * @inheritdoc
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isEnabled()
    {
        return $this->active;
    }

    /**
     * @return bool
     */
    public function isVisible(): bool
    {
        return $this->visible;
    }

    /**
     * @param bool $visible
     */
    public function setVisible(bool $visible)
    {
        $this->visible = $visible;
    }

    /**
     * @return ArrayCollection
     */
    public function getAscents()
    {
        return $this->ascents;
    }

    /**
     * @param ArrayCollection $ascents
     */
    public function setAscents($ascents)
    {
        $this->ascents = $ascents;
    }

    /**
     * @param Boulder $boulder
     */
    public function addBoulder(Boulder $boulder)
    {
        $this->boulders->add($boulder);
    }

    /**
     * @return ArrayCollection
     */
    public function getBoulders()
    {
        return $this->boulders;
    }

    /**
     * @param ArrayCollection $boulders
     */
    public function setBoulders(ArrayCollection $boulders)
    {
        $this->boulders = $boulders;
    }

    /**
     * @return UploadedFile
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * @param UploadedFile $media
     */
    public function setMedia($media)
    {
        $this->media = $media;
    }

    /**
     * @return Event[]
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * @param Event[] $events
     */
    public function setEvents(array $events)
    {
        $this->events = $events;
    }

    /**
     * @return Location[]
     */
    public function getTenants()
    {
        return $this->tenants;
    }

    /**
     * @param Location[] $tenants
     */
    public function setTenants(array $tenants)
    {
        $this->tenants = $tenants;
    }

    /**
     * @param Location $tenant
     */
    public function addTenant(Location $tenant)
    {
        $this->tenants->add($tenant);
    }

    /**
     * @param Location $tenant
     */
    public function removeTenant(Location $tenant)
    {
        $this->tenants->remove($tenant);
    }

    /**
     * @return int
     */
    public function getLastVisitedLocation()
    {
        return $this->lastVisitedLocation;
    }

    /**
     * @param int $lastVisitedLocation
     */
    public function setLastVisitedLocation(int $lastVisitedLocation)
    {
        $this->lastVisitedLocation = $lastVisitedLocation;
    }

    /**
     * @return array
     */
    public function getMigrations()
    {
        return $this->migrations;
    }

    /**
     * @param array $migrations
     */
    public function setMigrations(array $migrations)
    {
        $this->migrations = $migrations;
    }

    /**
     * @param string $migration
     */
    public function addMigration(string $migration)
    {
        $this->migrations[] = $migration;
    }

    public function isEqualTo(UserInterface $user)
    {
        return $this->getUsername() === $user->getUsername();
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(int $weight): void
    {
        $this->weight = $weight;
    }
}
