<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class BoulderRating
{
    public const RESOURCE_NAME = "BoulderRating";

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id")
     */
    private ?User $author = null;

    /**
     * @ORM\ManyToOne(targetEntity="Boulder")
     * @ORM\JoinColumn(name="boulder_id", referencedColumnName="id")
     */
    private ?Boulder $boulder = null;

    /**
     * @ORM\Column(type="integer")
     */
    private ?int $rating = null;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    private ?string $checksum = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): void
    {
        $this->author = $author;
    }

    public function getBoulder(): ?Boulder
    {
        return $this->boulder;
    }

    public function setBoulder(?Boulder $boulder): void
    {
        $this->boulder = $boulder;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(?int $rating): void
    {
        $this->rating = $rating;
    }

    public function getChecksum(): ?string
    {
        return $this->checksum;
    }

    public function setChecksum(): void
    {
        $this->checksum = md5($this->boulder->getId() . $this->author->getId());;
    }
}