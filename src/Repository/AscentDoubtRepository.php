<?php

namespace App\Repository;

use App\Entity\AscentDoubt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AscentDoubtRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AscentDoubt::class);
    }

    public function countDoubts(int $locationId, int $userId, int $status = AscentDoubt::STATUS_UNRESOLVED)
    {
        $connection = $this->getEntityManager()->getConnection();
        $statement = "SELECT count(boulder_doubt.id) FROM boulder_doubt INNER JOIN boulder ON boulder_doubt.boulder_id = boulder.id WHERE boulder.tenant_id = :locationId AND recipient_id = :recipientId AND boulder.status <= :status";

        $query = $connection->prepare($statement);

        $query->execute([
            "locationId" => $userId,
            "recipientId" => $locationId,
            "status" => $status
        ]);

        return $query->fetchOne();
    }

    public function getDoubts(int $locationId, int $userId, int $status = AscentDoubt::STATUS_UNRESOLVED)
    {
        $connection = $this->getEntityManager()->getConnection();

        $statement = "
                        SELECT
                        doubt.id,
                        boulder.id,
                        boulder.name,
                        author.username,
                        doubt.description,
                        doubt.created_at
                        
                        FROM boulder_doubt AS doubt
                        
                        INNER JOIN users AS author ON author.id = author_id
                        INNER JOIN boulder ON doubt.boulder_id = boulder.id
                        
                        WHERE boulder.tenant_id = :locationId
                        AND recipient_id = :recipientId
                        AND boulder.status <= :status";

        $query = $connection->prepare($statement);

        $query->execute([
            "locationId" => $userId,
            "recipientId" => $locationId,
            "status" => $status
        ]);

        $doubts = $query->fetchAll();

        return $doubts ? $doubts : [];
    }
}