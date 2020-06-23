<?php

namespace App\Struct;


class AscentStruct
{
    private string $id;

    private string $type;

    private UserStruct $user;

    private float $score;

    public static function fromArray(array $data)
    {
        $self = new self();

        if (isset($data['id'])) {
            $self->setId($data['id']);
        }

        if (isset($data['type'])) {
            $self->setType($data['type']);
        }

        if (isset($data['user'])) {
            $self->setUser(UserStruct::fromArray($data['user']));
        }

        if (isset($data['score'])) {
            $self->setScore($data['score']);
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

    public function getType(): string
    {
        return $this->type;
    }

    public function isType(string $type): bool
    {
        return $type === $this->getType();
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getUser(): UserStruct
    {
        return $this->user;
    }

    public function setUser(UserStruct $user): void
    {
        $this->user = $user;
    }

    public function getScore(): float
    {
        return $this->score;
    }

    public function setScore(float $score): void
    {
        $this->score = $score;
    }
}