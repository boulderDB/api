<?php

namespace App\Entity;

use App\Components\Entity\LocationResourceInterface;
use App\Components\Entity\LocationTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 */
class Area implements LocationResourceInterface
{
    use LocationTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Entity\Wall", fetch="LAZY", inversedBy="areas")
     * @ORM\JoinTable(name="area_walls")
     */
    private $walls;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $gradeDiversityTargetMap;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @return ArrayCollection
     */
    public function getWalls()
    {
        return $this->walls;
    }

    /**
     * @param ArrayCollection $walls
     */
    public function setWalls(ArrayCollection $walls)
    {
        $this->walls = $walls;
    }

    public function getGradeDiversityTargetMap()
    {
        return $this->gradeDiversityTargetMap;
    }

    /**
     * @param string $gradeDiversityTargetMap
     */
    public function setGradeDiversityTargetMap(string $gradeDiversityTargetMap)
    {
        $this->gradeDiversityTargetMap = $gradeDiversityTargetMap;
    }
}