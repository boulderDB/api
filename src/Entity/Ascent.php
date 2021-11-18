<?php

namespace App\Entity;

use App\Helper\Behaviours;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(indexes={@ORM\Index(name="user", columns={"user_id"})})
 */
class Ascent implements LocationResourceInterface, TimestampableInterface, UserResourceInterface, CacheableInterface
{
    public const RESOURCE_NAME = "ascent";

    public const ASCENT_TOP = 'top';
    public const ASCENT_FLASH = 'flash';
    public const ASCENT_RESIGNED = 'resignation';
    public const PENDING_DOUBT_FLAG = '-pending-doubt';

    use TimestampTrait;
    use LocationTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity="Boulder", inversedBy="ascents")
     * @ORM\JoinColumn(name="boulder_id", referencedColumnName="id")
     */
    private ?Boulder $boulder = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="ascents")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private ?User $user = null;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private ?string $type = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $score = null;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    private ?string $checksum = null;

    /**
     * @ORM\Column(name="user_id", type="integer")
     */
    private ?int $userId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBoulder(): ?Boulder
    {
        return $this->boulder;
    }

    public function setBoulder(?Boulder $boulder): void
    {
        $this->boulder = $boulder;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(?int $score): void
    {
        $this->score = $score;
    }

    public function isType(string $type): bool
    {
        return $type === $this->type;
    }

    public function setDoubted(): void
    {
        $this->type = $this->type . Ascent::PENDING_DOUBT_FLAG;
    }

    public function isDoubted(): bool
    {
        return str_contains($this->type, Ascent::PENDING_DOUBT_FLAG);
    }

    public function setChecksum(): void
    {
        $this->checksum = md5($this->boulder->getId() . $this->user->getId());
    }

    public function invalidates(): array
    {
        return [
            "/boulders"
        ];
    }

    public function getBehaviours(): array
    {
        return Behaviours::getInterfaces($this);
    }
}
