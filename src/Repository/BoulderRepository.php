<?php

namespace App\Repository;

use App\Entity\Boulder;
use App\Entity\Grade;
use App\Service\Serializer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\Persistence\ManagerRegistry;

class BoulderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Boulder::class);
    }

    public function getOne(int $id): ?Boulder
    {
        return $this->createQueryBuilder("boulder")
            ->innerJoin("boulder.holdType", "holdType")
            ->innerJoin("boulder.grade", "grade")
            ->innerJoin("boulder.internalGrade", "internalGrade")
            ->innerJoin("boulder.startWall", "startWall")
            ->leftJoin("boulder.endWall", "endWall")
            ->leftJoin("boulder.setters", "setter")
            ->leftJoin("boulder.tags", "tag")
            ->leftJoin("boulder.comments", "comments")
            ->leftJoin("boulder.ratings", "rating")
            ->where("boulder.id = :id")
            ->setParameter("id", $id)
            ->getQuery()
            ->setFetchMode(Grade::class, "grade", ClassMetadataInfo::FETCH_EAGER)
            ->getOneOrNullResult();
    }

    public function countActive(int $locationId, bool $active = true): int
    {
        $queryBuilder = $this->createQueryBuilder('boulder')
            ->select("count(boulder.id)")
            ->where("boulder.location = :location")
            ->setParameter("location", $locationId);

        if ($active) {
            $queryBuilder
                ->andWhere("boulder.status = :status")
                ->setParameter("status", Boulder::STATUS_ACTIVE);
        }

        $result = $queryBuilder
            ->getQuery()
            ->getSingleResult();

        return $result ? $result[1] : 0;
    }

    public function getAll(int $locationId): ?array
    {
        $partials = [
            "partial boulder.{id, name, createdAt}",
            "partial startWall.{id}",
            "partial endWall.{id}",
            "partial setter.{id}",
            "partial holdType.{id}",
            "partial grade.{id}",
            "partial internalGrade.{id}"
        ];

        $queryBuilder = $this->createQueryBuilder('boulder')
            ->select(implode(", ", $partials))
            ->leftJoin("boulder.setters", "setter")
            ->leftJoin("boulder.startWall", "startWall")
            ->leftJoin("boulder.endWall", "endWall")
            ->innerJoin("boulder.grade", "grade")
            ->leftJoin("boulder.internalGrade", "internalGrade")
            ->innerJoin("boulder.holdType", "holdType");

        return $queryBuilder
            ->where("boulder.location = :location")
            ->andWhere("boulder.status = :status")
            ->setParameter("location", $locationId)
            ->setParameter("status", Boulder::STATUS_ACTIVE)
            ->getQuery()
            ->getArrayResult();
    }

    public function getWithAscents(string $locationId): array
    {
        return $this->createQueryBuilder('boulder')
            ->select('
                partial boulder.{id, points},
                partial ascent.{id, type},
                partial user.{id, username, gender, lastActivity, image, visible}
            ')
            ->innerJoin('boulder.ascents', 'ascent')
            ->innerJoin('ascent.user', 'user')
            ->where('boulder.location = :location')
            ->andWhere('boulder.status = :status')
            ->andWhere('user.visible = :visible')
            ->setParameter('location', $locationId)
            ->setParameter('status', Boulder::STATUS_ACTIVE)
            ->setParameter('visible', true)
            ->getQuery()
            ->getResult();
    }
}
