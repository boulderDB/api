<?php

namespace App\Repository;

use App\Entity\Boulder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class BoulderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Boulder::class);
    }

    public function getAscentData(string $locationId): array
    {
        return $this->createQueryBuilder('boulder')
            ->select('
                partial boulder.{id, points},
                partial ascent.{id, type},
                partial user.{id, username, gender, lastActivity, media, visible}
            ')
            ->innerJoin('boulder.ascents', 'ascent')
            ->innerJoin('ascent.user', 'user')
            ->where('boulder.location = :location')
            ->andWhere('boulder.status = :status')
            ->andWhere('boulder.user :visible')
            ->setParameter('location', $locationId)
            ->setParameter('status', Boulder::STATUS_ACTIVE)
            ->setParameter('visible', true)
            ->getQuery()
            ->getArrayResult();
    }
}