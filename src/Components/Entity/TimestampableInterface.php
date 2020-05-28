<?php

namespace App\Components\Entity;

interface TimestampableInterface
{
    public function getCreatedAt(): ?\DateTime;

    public function setCreatedAt(\DateTimeInterface $createdAt = null);

    public function getUpdatedAt(): ?\DateTime;

    public function setUpdatedAt(\DateTimeInterface $updatedAt = null);
}