<?php

namespace App\Repository;

use App\Entity\BoulderError;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BoulderErrorRepository extends ServiceEntityRepository
{
    use FilterableRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BoulderError::class);
    }

    public function findByStatus(int $locationId, string $status = BoulderError::STATUS_UNRESOLVED)
    {
        return $this->createQueryBuilder("boulderError")
            ->select(["boulderError", "boulder"])
            ->leftJoin("boulderError.author", "author")
            ->leftJoin("boulderError.boulder", "boulder")
            ->leftJoin("boulder.startWall", "startWall")
            ->where("boulderError.location = :location")
            ->andWhere("boulderError.status = :status")
            ->setParameter("location", $locationId)
            ->setParameter("status", $status)
            ->getQuery()
            ->getResult();
    }

    public function countByStatus(int $locationId, string $status = BoulderError::STATUS_UNRESOLVED)
    {
        return $this->createQueryBuilder("boulderError")
            ->select("count(boulderError.id)")
            ->where("boulderError.location = :location")
            ->andWhere("boulderError.status = :status")
            ->setParameter("location", $locationId)
            ->setParameter("status", $status)
            ->getQuery()
            ->getSingleScalarResult();
    }
}