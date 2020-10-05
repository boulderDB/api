<?php

namespace App\Entity;


use App\Entity\User;

interface UserResourceInterface
{
    public function getUser(): ?User;
}