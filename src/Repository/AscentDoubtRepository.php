<?php

namespace App\Repository;

use App\Entity\AscentDoubt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AscentDoubtRepository extends ServiceEntityRepository
{
    use FilterableRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AscentDoubt::class);
    }

    public function getByStatus(int $userId, int $locationId, string $status = AscentDoubt::STATUS_UNRESOLVED)
    {
        return $this->createQueryBuilder("ascentDoubt")
            ->select(["ascentDoubt", "recipient", "boulder"])
            ->innerJoin("ascentDoubt.recipient", "recipient")
            ->innerJoin("ascentDoubt.boulder", "boulder")
            ->where("recipient.id = :recipientId")
            ->andWhere("ascentDoubt.status <= :status")
            ->andWhere("boulder.location = :locationId")
            ->setParameters([
                "recipientId" => $userId,
                "status" => $status,
                "locationId" => $locationId
            ])
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