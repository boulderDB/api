<?php

namespace App\Repository;

use App\Entity\HoldType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class HoldTypeRepository extends ServiceEntityRepository implements DeactivatableRepositoryInterface, FilterableRepositoryInterface
{
    use FilterableRepositoryTrait;
    use DeactivatableRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HoldType::class);
    }
}