<?php

namespace App\Entity;

use App\Components\Entity\TenantResourceInterface;
use App\Components\Entity\TenantTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @ORM\Entity()
 */
class Wall implements TenantResourceInterface
{
    use TenantTrait;

    /**
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
     * @var string
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Boulder", mappedBy="startWall", fetch="LAZY")
     */
    private $boulders;

    /**
     * @var UploadedFile
     * @ORM\Column(type="string", nullable=true)
     */
    private $media;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Area", mappedBy="walls", fetch="LAZY")
     */
    private $areas;

    public function __construct()
    {
        $this->boulders = new ArrayCollection();
        $this->areas = new ArrayCollection();
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
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
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
     * @param Boulder $boulder
     */
    public function addBoulder(Boulder $boulder)
    {
        $this->boulders->add($boulder);
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

    public function getActiveBoulders()
    {
        return $this->getBoulders()->filter(function (Boulder $boulder) {

            /**
             * @var Boulder
             */
            return $boulder->getStatus() === Boulder::STATUS_ACTIVE;
        });
    }
}
