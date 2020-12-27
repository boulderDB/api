<?php

namespace App\Entity;

interface UserResourceInterface
{
    public function getUser(): ?User;
}