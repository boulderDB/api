<?php

namespace App\Components\Entity;

use App\Entity\User;

interface UserResourceInterface
{
    public function getUser(): ?User;
}