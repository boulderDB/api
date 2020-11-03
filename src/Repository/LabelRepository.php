<?php

namespace App\Repository;

use App\Entity\Label;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LabelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Label::class);
    }

    /**
     * @return Label[]
     */
    public function findByUser(string $userId)
    {
        return $this
            ->createQueryBuilder("label")
            ->where("label.user = :userId")
            ->setParameter("userId", $userId)
            ->getQuery()
            ->getResult();
    }
}
