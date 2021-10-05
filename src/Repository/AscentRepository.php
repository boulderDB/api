<?php

namespace App\Repository;

use App\Entity\Ascent;
use App\Entity\Boulder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AscentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ascent::class);
    }

    /**
     * @return Ascent[]
     */
    public function getByUserAndLocation(int $userId, int $locationId)
    {
        return $this->createQueryBuilder("ascent")
            ->innerJoin("ascent.boulder", "boulder")
            ->where("boulder.location = :location")
            ->andWhere("boulder.status = :status")
            ->andWhere("ascent.user = :user")
            ->setParameter("location", $locationId)
            ->setParameter("status", Boulder::STATUS_ACTIVE)
            ->setParameter("user", $userId)
            ->getQuery()
            ->getResult();
    }
}