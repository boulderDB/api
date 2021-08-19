<?php

namespace App\Repository;

use App\Entity\Location;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LocationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Location::class);
    }

    public function getPublic()
    {
        return $this->createQueryBuilder("location")
            ->where("location.public = true")
            ->getQuery()
            ->getResult();
    }

    public function getPublicById(int $id)
    {
        return $this->createQueryBuilder("location")
            ->where("location.id = :id")
            ->andWhere("location.public = true")
            ->setParameter("id", $id)
            ->getQuery()
            ->getResult();
    }
}