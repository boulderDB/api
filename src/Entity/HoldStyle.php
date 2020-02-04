<?php

namespace App\Entity;

use App\Components\Entity\TenantResourceInterface;
use App\Components\Entity\TenantTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Entity()
 * @ORM\Table(name="hold_color")
 */
class HoldStyle implements TenantResourceInterface
{
    use TenantTrait;

    /**
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    private $name;

    /**
     * @var UploadedFile
     * @ORM\Column(type="string", nullable=true)
     */
    private $media;

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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
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
     * @return int
     */
    public function getTenantId()
    {
        return $this->tenant->getId();
    }
}
