<?php

namespace App\Repository;

use App\Entity\HoldType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class HoldTypeRepository extends ServiceEntityRepository
{
    use FilterTrait;
    use DeactivatableTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HoldType::class);
    }
}