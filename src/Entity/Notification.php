<?php

namespace App\Entity;

use App\Helper\Behaviours;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Notification implements LocationResourceInterface
{
    use LocationTrait;

    public const RESOURCE_NAME = "notification";

    public const TYPE_DOUBT = "doubt";
    public const TYPE_ERROR = "error";
    public const TYPE_COMMENT = "comment";

    public const TYPES = [
        self::TYPE_DOUBT,
        self::TYPE_ERROR,
        self::TYPE_COMMENT
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string")
     */
    private ?string $type;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private ?array $roles = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getRoles(): ?array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public static function getChecksum(int $locationId, string $type, string $role): string
    {
        return md5($locationId . $type . $role);
    }

    public function getBehaviours(): array
    {
        return Behaviours::getInterfaces($this);
    }
}