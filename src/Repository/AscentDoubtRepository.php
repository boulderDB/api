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

    public function countDoubts(int $locationId, int $userId, int $statusSmallerThan = AscentDoubt::STATUS_READ)
    {
        $connection = $this->getEntityManager()->getConnection();
        $statement = "SELECT count(boulder_doubt.id) FROM boulder_doubt INNER JOIN boulder ON boulder_doubt.boulder_id = boulder.id WHERE boulder.tenant_id = :locationId AND recipient_id = :recipientId AND boulder_doubt.status <= :status";

        $query = $connection->prepare($statement);

        $query->execute([
            "locationId" => $locationId,
            "recipientId" => $userId,
            "status" => $statusSmallerThan
        ]);

        return $query->fetchOne();
    }

    public function getDoubts(int $locationId, int $userId, int $statusSmallerThan = AscentDoubt::STATUS_READ)
    {
        $connection = $this->getEntityManager()->getConnection();

        $statement = "
                        SELECT
                        doubt.id AS id,
                        
                        boulder.id AS boulder_id,
                        boulder.name AS boulder_name,
                        
                        ascent.type AS ascent_type,
                        
                        author.id AS author_id,
                        author.username AS author_username,
                        
                        doubt.description AS doubt_description,
                        doubt.created_at AS doubt_created_at
                        
                        FROM boulder_doubt AS doubt
                        
                        INNER JOIN users AS author ON author.id = author_id
                        INNER JOIN boulder ON doubt.boulder_id = boulder.id
                        INNER JOIN ascent ON boulder.id = ascent.boulder_id AND ascent.user_id = :recipientId
                        
                        WHERE boulder.tenant_id = :locationId
                        AND recipient_id = :recipientId
                        AND doubt.status <= :status";

        $query = $connection->prepare($statement);

        $query->execute([
            "locationId" => $locationId,
            "recipientId" => $userId,
            "status" => $statusSmallerThan
        ]);

        $doubts = $query->fetchAll();

        return $doubts ? $doubts : [];
    }
}