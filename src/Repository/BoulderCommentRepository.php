<?php

namespace App\Repository;

use App\Entity\BoulderComment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BoulderCommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BoulderComment::class);
    }

    public function findForActiveBoulders(int $locationId)
    {
        return $this->createQueryBuilder("boulderComment")
            ->select(["boulderComment", "boulder"])
            ->leftJoin("boulderComment.author", "author")
            ->leftJoin("boulderComment.boulder", "boulder")
            ->where("boulderComment.location = :location")
            ->andWhere("boulder.status = :status")
            ->setParameter("location", $locationId)
            ->setParameter("status", "active")
            ->getQuery()
            ->getResult();
    }
}