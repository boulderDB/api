<?php

namespace App\Repository;

use App\Entity\Setter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SetterRepository extends ServiceEntityRepository implements DeactivatableRepositoryInterface, FilterableRepositoryInterface
{
    use FilterableRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Setter::class);
    }

    public function queryWhere(int $locationId, array $config, array $filters)
    {
        $queryBuilder = $this->createQueryBuilder("setter")
            ->innerJoin("setter.locations", "location")
            ->where("location.id = :locationId")
            ->setParameter("locationId", $locationId);

        $this->addFilters($queryBuilder, "setter", $config, $filters);

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }

    public function getCurrent(int $locationId)
    {
        return $this->createQueryBuilder("setter")
            ->innerJoin("setter.locations", "location")
            ->innerJoin("setter.boulders", "boulder")
            ->where("location.id = :locationId")
            ->andWhere("setter.active = true")
            ->andWhere("boulder.status = :status")
            ->setParameters([
                "locationId" => $locationId,
                "status" => "active"
            ])
            ->getQuery()
            ->getResult();
    }

    public function exists(string $property, string $value, int $locationId): bool
    {
        $connection = $this->getEntityManager()->getConnection();
        $statement = "SELECT id FROM setter WHERE lower({$property}) = lower(:property)";

        $query = $connection->prepare($statement);

        $result = $query->executeQuery([
            "property" => strtolower($value)
        ])->fetchOne();

        return (bool)$result;
    }

    public function getActive(int $locationId)
    {
        return $this->createQueryBuilder("setter")
            ->innerJoin("setter.locations", "location")
            ->where("location = :locationId")
            ->andWhere("setter.active = true")
            ->setParameter("locationId", $locationId)
            ->getQuery()
            ->getResult();
    }

    public function getAll(int $locationId)
    {
        return $this->createQueryBuilder("setter")
            ->innerJoin("setter.locations", "location")
            ->where("location = :locationId")
            ->setParameter("locationId", $locationId)
            ->getQuery()
            ->getResult();
    }
}
