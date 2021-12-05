<?php

namespace App\Repository;

interface DeactivatableRepositoryInterface
{
    public function getActive(int $locationId);

    public function getAll(int $locationId);
}