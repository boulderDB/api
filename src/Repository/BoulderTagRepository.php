<?php

namespace App\Repository;

use App\Entity\BoulderTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BoulderTagRepository extends ServiceEntityRepository
{
    use FilterTrait;
    use DeactivatableTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BoulderTag::class);
    }
}