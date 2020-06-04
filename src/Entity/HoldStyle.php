<?php

namespace App\Entity;

use App\Components\Entity\LocationResourceInterface;
use App\Components\Entity\LocationTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="hold_color")
 */
class HoldStyle implements LocationResourceInterface
{
    use LocationTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    private $name;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $media;

    /**
     * @var string
     * @ORM\Column(name="icon", type="string", nullable=true)
     */
    private $icon;

    public function getId()
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getMedia()
    {
        return $this->media;
    }

    public function setMedia($media)
    {
        $this->media = $media;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): void
    {
        $this->icon = $icon;
    }
}
