<?php

namespace App\Repository;

use App\Entity\Area;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AreaRepository extends ServiceEntityRepository implements DeactivatableRepositoryInterface
{
    use FilterableRepositoryTrait;
    use DeactivatableRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Area::class);
    }
}