<?php

namespace App\Repository;

use App\Entity\BoulderComment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BoulderCommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BoulderComment::class);
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getLatest(string $filter = "active", int $limit = 10): array
    {
        $statement = "SELECT 
            boulder_comment.id, 
            boulder_comment.description,
            boulder_comment.created_at,
            boulder_comment.boulder_id,
            users.username AS author 
            
            FROM boulder_comment
            
            INNER JOIN users ON boulder_comment.author_id = users.id
            INNER JOIN boulder ON boulder_comment.boulder_id = boulder.id
            
            WHERE boulder.status = :status
            LIMIT :limit";

        $query = $this->getEntityManager()->getConnection()->prepare($statement);

        $query->execute([
            "limit" => $limit,
            "status" => $filter
        ]);

        return $query->fetchAllAssociative();
    }
}