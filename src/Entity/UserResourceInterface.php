<?php

namespace App\Entity;

interface UserResourceInterface
{
    public function getOwner(): ?User;
}