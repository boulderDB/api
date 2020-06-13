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

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $city;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $zip;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $addressLineOne;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $addressLineTwo;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $countryCode;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $image;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $website;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $facebook;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $instagram;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $twitter;

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

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function getZip(): ?string
    {
        return $this->zip;
    }

    public function setZip(string $zip): void
    {
        $this->zip = $zip;
    }

    public function getAddressLineOne(): ?string
    {
        return $this->addressLineOne;
    }

    public function setAddressLineOne(string $addressLineOne): void
    {
        $this->addressLineOne = $addressLineOne;
    }

    public function getAddressLineTwo(): ?string
    {
        return $this->addressLineTwo;
    }

    public function setAddressLineTwo(string $addressLineTwo): void
    {
        $this->addressLineTwo = $addressLineTwo;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(string $countryCode): void
    {
        $this->countryCode = $countryCode;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): void
    {
        $this->image = $image;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(string $website): void
    {
        $this->website = $website;
    }

    public function getFacebook(): ?string
    {
        return $this->facebook;
    }

    public function setFacebook(string $facebook): void
    {
        $this->facebook = $facebook;
    }

    public function getInstagram(): ?string
    {
        return $this->instagram;
    }

    public function setInstagram(string $instagram): void
    {
        $this->instagram = $instagram;
    }

    public function getTwitter(): ?string
    {
        return $this->twitter;
    }

    public function setTwitter(string $twitter): void
    {
        $this->twitter = $twitter;
    }
}