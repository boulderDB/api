<?php

namespace App\Repository;

use App\Entity\BoulderTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BoulderTagRepository extends ServiceEntityRepository implements DeactivatableRepositoryInterface
{
    use FilterableRepositoryTrait;
    use DeactivatableRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BoulderTag::class);
    }
}