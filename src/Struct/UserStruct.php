<?php


namespace App\Struct;


class UserStruct
{
    private string $id;

    private string $username;

    private string $gender;

    private \DateTime $lastActivity;

    private ?string $image = null;

    public static function fromArray(array $data)
    {
        $self = new self();

        if (isset($data['id'])) {
            $self->setId($data['id']);
        }

        if (isset($data['username'])) {
            $self->setUsername($data['username']);
        }

        if (isset($data['gender'])) {
            $self->setGender($data['gender']);
        }

        if (isset($data['lastActivity'])) {

            if (is_string($data['lastActivity'])) {
                $self->setLastActivity(\DateTime::createFromFormat('c', $data['lastActivity']));
            } else if ($data['lastActivity'] instanceof \DateTime) {
                $self->setLastActivity($data['lastActivity']);
            } else {
                throw new \InvalidArgumentException("Unable to handle date: {$data['lastActivity']}");
            }
        }

        if (isset($data['image'])) {
            $self->setImage($data['image']);
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

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getGender(): string
    {
        return $this->gender;
    }

    public function setGender(string $gender): void
    {
        $this->gender = $gender;
    }

    public function getLastActivity(): \DateTime
    {
        return $this->lastActivity;
    }

    public function setLastActivity(\DateTime $lastActivity): void
    {
        $this->lastActivity = $lastActivity;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): void
    {
        $this->image = $image;
    }
}
