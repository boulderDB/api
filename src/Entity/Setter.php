<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Setter implements TimestampableInterface
{
    use TimestampTrait;

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
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", unique=true)
     */
    private ?User $user = null;

    /**
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private bool $active = true;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Location", fetch="LAZY", inversedBy="setters")
     * @ORM\JoinTable(name="setter_locations",
     *     joinColumns={
     *         @ORM\JoinColumn(name="setter_id", referencedColumnName="id"),
     *      },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="location_id", referencedColumnName="id")
     *     }
     * )
     */
    private ?Collection $locations = null;

    public function __construct()
    {
        $this->locations = new ArrayCollection();
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
        $this->setUsername($user->getUsername());
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getLocations(): ?Collection
    {
        return $this->locations;
    }

    public function setLocations(?Collection $locations): void
    {
        $this->locations = $locations;
    }
}
