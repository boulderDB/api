<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function getActivePastHalfYear()
    {
        $from = new \DateTime();
        $from->modify('-6 months');

        return $this->createQueryBuilder('user')
            ->andWhere('user.lastActivity > :from')
            ->setParameter('from', $from)
            ->getQuery()
            ->getResult();
    }
}