<?php

namespace App\Entity;

use App\Helper\Behaviours;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\Collection;

/**
 * @ORM\Entity()
 */
class Notification implements LocationResourceInterface
{
    public const RESOURCE_NAME = "notification";

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
     * @ORM\Column(type="string")
     */
    private ?string $type = null;



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

    public static function getAdminTypes(): array
    {
        return [
            self::TYPE_ERROR,
            self::TYPE_COMMENT
        ];
    }

    public function getIdentifier(): string
    {
        return $this->getType() . "@" . $this->getLocation()->getId();
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