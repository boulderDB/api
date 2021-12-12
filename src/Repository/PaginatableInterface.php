<?php

namespace App\Repository;

interface PaginatableInterface
{
    public function getTotalItemsCount(array $parameters): int;

    public function paginate(int $page, array $parameters = [], int $size = 50): array;
}