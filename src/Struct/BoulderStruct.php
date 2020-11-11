<?php

namespace App\Struct;

use App\Entity\Boulder;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class BoulderStruct
{
    private string $id;

    private string $points;

    private Collection $ascents;

    public static function fromInstance(Boulder $boulder)
    {
        
    }

    public static function fromArray(array $data)
    {
        $self = new self();

        if (isset($data['id'])) {
            $self->setId($data['id']);
        }

        if (isset($data['points'])) {
            $self->setPoints($data['points']);
        }

        if (isset($data['ascents'])) {
            $ascents = new ArrayCollection(
                array_map(function ($ascent) {
                    return AscentStruct::fromArray($ascent);
                }, $data['ascents'])
            );

            $self->setAscents($ascents);
        }

        return $self;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getPoints(): string
    {
        return $this->points;
    }

    public function setPoints(string $points): void
    {
        $this->points = $points;
    }

    public function getAscents(): Collection
    {
        return $this->ascents;
    }

    public function setAscents(Collection $ascents): void
    {
        $this->ascents = $ascents;
    }
}
