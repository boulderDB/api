<?php

namespace App\Repository;

use App\Entity\Boulder;
use App\Entity\ReadableIdentifier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class ReadableIdentifierRepository extends ServiceEntityRepository
{
    use DeactivatableRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReadableIdentifier::class);
    }

    public function getUnassigned(int $locationId)
    {
        $queryBuilder = $this->createQueryBuilder("readableIdentifier");
        self::addUnassignedQuery($queryBuilder, $locationId);

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }

    public static function addUnassignedQuery(QueryBuilder $queryBuilder, string $locationId)
    {
        return $queryBuilder->leftJoin(Boulder::class, "boulder", Join::WITH, "readableIdentifier.id = boulder.readableIdentifier")
            ->where("boulder.readableIdentifier is NULL")
            ->andWhere("readableIdentifier.location = :location")
            ->setParameter("location", $locationId);
    }
}