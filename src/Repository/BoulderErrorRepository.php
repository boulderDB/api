<?php

namespace App\Repository;

use App\Entity\Boulder;
use App\Entity\BoulderError;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BoulderErrorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BoulderError::class);
    }

    public function findByStatus(int $locationId, string $status = BoulderError::STATUS_UNRESOLVED)
    {
        return $this
            ->createQueryBuilder("boulderError")
            ->select("
                partial boulderError.{id, description, createdAt, location}, 
                partial author.{id, username}, 
                partial boulder.{id, name, startWall},
                partial startWall.{id, name}
            ")
            ->from(BoulderError::class, "boulderError")
            ->leftJoin("boulderError.author", "author")
            ->leftJoin("boulderError.boulder", "boulder")
            ->leftJoin("boulder.startWall", "startWall")
            ->where("boulderError.location = :location")
            ->andWhere("boulderError.status = :status")
            ->setParameter("location", $locationId)
            ->setParameter("status", $status)
            ->getQuery()
            ->getArrayResult();
    }
}