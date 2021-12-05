<?php

namespace App\Repository;

use App\Entity\Wall;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class WallRepository extends ServiceEntityRepository implements DeactivatableRepositoryInterface
{
    use FilterableRepositoryTrait;
    use DeactivatableRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wall::class);
    }
}