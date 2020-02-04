<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="tenant")
 */
class Location
{
    /**
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", unique=true)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(type="string", unique=true)
     */
    private $url;

    /**
     * @var boolean
     * @ORM\Column(type="boolean")
     */
    private $public;

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

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl(string $url)
    {
        $this->url = $url;
    }

    public function isPublic()
    {
        return $this->public;
    }

    public function setPublic(bool $public)
    {
        $this->public = $public;
    }
}