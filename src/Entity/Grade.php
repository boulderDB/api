<?php

namespace App\Entity;

use App\Components\Entity\LocationResourceInterface;
use App\Components\Entity\LocationTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Grade implements LocationResourceInterface
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
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    private $name;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=false)
     */
    private $position;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $public;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $color;

    /**
     * @var Grade
     * @ORM\ManyToOne(targetEntity="Grade")
     * @ORM\JoinColumn(name="external_grade", referencedColumnName="id")
     */
    private $externalGradeMapping;

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
    public function setId($id)
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
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition(int $position)
    {
        $this->position = $position;
    }

    /**
     * @return bool
     */
    public function isPublic()
    {
        return $this->public;
    }

    /**
     * @param bool $public
     */
    public function setPublic(bool $public)
    {
        $this->public = $public;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param string $color
     */
    public function setColor(string $color)
    {
        $this->color = $color;
    }

    public function getExternalGradeMapping()
    {
        return $this->externalGradeMapping;
    }

    /**
     * @param Grade $externalGradeMapping
     */
    public function setExternalGradeMapping(Grade $externalGradeMapping)
    {
        $this->externalGradeMapping = $externalGradeMapping;
    }
}
