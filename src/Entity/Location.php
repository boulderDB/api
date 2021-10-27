<?php

namespace App\Entity;

use App\Helper\Behaviours;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="tenant")
 */
class Location implements CacheableInterface
{
    public const RESOURCE_NAME = "Location";

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    private ?string $name = null;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    private ?string $url = null;

    /**
     * @ORM\Column(type="boolean")
     */
    private ?string $public = null;

    /**
     * @ORM\Column(type="string")
     */
    private ?string $city = null;

    /**
     * @ORM\Column(type="string")
     */
    private ?string $zip = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $addressLineOne = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $addressLineTwo = null;

    /**
     * @ORM\Column(type="string")
     */
    private ?string $countryCode = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $image = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $website = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $facebook = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $instagram = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $twitter = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    public function getPublic(): ?string
    {
        return $this->public;
    }

    public function setPublic(?string $public): void
    {
        $this->public = $public;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function getZip(): ?string
    {
        return $this->zip;
    }

    public function setZip(?string $zip): void
    {
        $this->zip = $zip;
    }

    public function getAddressLineOne(): ?string
    {
        return $this->addressLineOne;
    }

    public function setAddressLineOne(?string $addressLineOne): void
    {
        $this->addressLineOne = $addressLineOne;
    }

    public function getAddressLineTwo(): ?string
    {
        return $this->addressLineTwo;
    }

    public function setAddressLineTwo(?string $addressLineTwo): void
    {
        $this->addressLineTwo = $addressLineTwo;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(?string $countryCode): void
    {
        $this->countryCode = $countryCode;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): void
    {
        $this->image = $image;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): void
    {
        $this->website = $website;
    }

    public function getFacebook(): ?string
    {
        return $this->facebook;
    }

    public function setFacebook(?string $facebook): void
    {
        $this->facebook = $facebook;
    }

    public function getInstagram(): ?string
    {
        return $this->instagram;
    }

    public function setInstagram(?string $instagram): void
    {
        $this->instagram = $instagram;
    }

    public function getTwitter(): ?string
    {
        return $this->twitter;
    }

    public function setTwitter(?string $twitter): void
    {
        $this->twitter = $twitter;
    }

    public function invalidates(): array
    {
        return [
            "/locations"
        ];
    }

    public function getBehaviours(): array
    {
        return Behaviours::getInterfaces($this);
    }
}
