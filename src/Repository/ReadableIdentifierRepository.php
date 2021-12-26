<?php

namespace App\Repository;

use App\Entity\ReadableIdentifier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReadableIdentifierRepository extends ServiceEntityRepository
{
    use DeactivatableRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReadableIdentifier::class);
    }
}