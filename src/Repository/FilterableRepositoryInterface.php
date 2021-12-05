<?php

namespace App\Repository;

interface FilterableRepositoryInterface
{
    public function queryWhere(int $locationId, array $config, array $filters);
}