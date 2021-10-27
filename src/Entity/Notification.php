<?php

namespace App\Entity;

use App\Helper\Behaviours;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Notification implements LocationResourceInterface
{
    public const RESOURCE_NAME = "Notification";

    use LocationTrait;

    public const TYPE_DOUBT = "doubt";
    public const TYPE_ERROR = "error";
    public const TYPE_COMMENT = "comment";

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="ascents")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private ?User $user = null;

    /**
     * @ORM\Column(type="string")
     */
    private ?string $type = null;

    /**
     * @ORM\Column(type="boolean", options={"default": true})
     */
    private bool $active = true;

    public function getId(): ?int
    {
        return $this->id;
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

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public static function getNotificationId(string $type, string $location): string
    {
        return "$type@$location";
    }

    public static function getAdminTypes(): array
    {
        return [
            self::TYPE_ERROR,
            self::TYPE_COMMENT
        ];
    }

    public static function getDefaultTypes(): array
    {
        return [self::TYPE_DOUBT];
    }

    public function getBehaviours(): array
    {
        return Behaviours::getInterfaces($this);
    }
}