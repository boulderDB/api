<?php

namespace App\Repository;

use App\Entity\AscentDoubt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AscentDoubtRepository extends ServiceEntityRepository
{
    use FilterTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AscentDoubt::class);
    }

    public function getByStatus(int $userId, string $status = AscentDoubt::STATUS_UNRESOLVED)
    {
        $queryBuilder = $this->createQueryBuilder("ascentDoubt");
        $queryBuilder
            ->innerJoin("ascentDoubt.recipient", "recipient")
            ->where("recipient.id = :recipientId")
            ->andWhere("ascentDoubt.status <= :status")
            ->setParameters([
                "recipientId" => $userId,
                "status" => $status
            ]);

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }

    public function countDoubts(int $locationId, int $userId, int $statusSmallerThan = AscentDoubt::STATUS_READ)
    {
        $connection = $this->getEntityManager()->getConnection();
        $statement = "SELECT count(boulder_doubt.id) FROM boulder_doubt INNER JOIN boulder ON boulder_doubt.boulder_id = boulder.id WHERE boulder.tenant_id = :locationId AND recipient_id = :recipientId AND boulder_doubt.status <= :status";

        $query = $connection->prepare($statement);

        $query->executeQuery([
            "locationId" => $locationId,
            "recipientId" => $userId,
            "status" => $statusSmallerThan
        ]);

        return $query->fetchOne();
    }
}