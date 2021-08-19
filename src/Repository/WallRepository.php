<?php

namespace App\Repository;

use App\Entity\Wall;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class WallRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Wall::class);
    }

    public function getActive(int $locationId): array
    {
        $connection = $this->getEntityManager()->getConnection();

        $query = $connection->prepare("SELECT id, name FROM wall WHERE tenant_id = :locationId AND active = :active");
        $query->execute([
            "locationId" => $locationId,
            "active" => true
        ]);

        return $query->fetchAllAssociative();
    }

    public function exists(int $id, int $locationId): bool
    {
        $statement = "SELECT id FROM wall WHERE id = :id AND wall.tenant_id = :locationId";

        $query = $this->getEntityManager()->getConnection()->prepare($statement);

        $query->execute([
            "id" => $id,
            "locationId" => $locationId,
        ]);

        return $query->fetchAllAssociative() ? true : false;
    }

    public function getDetail(int $id, int $locationId): ?array
    {
        $statement = "SELECT wall.id, wall.name, wall.media, description, count(boulder.id) as active_boulders FROM wall 
                      LEFT JOIN BOULDER on boulder.start_wall_id = wall.id 
                      WHERE wall.tenant_id = :locationId AND wall.active = true 
                      AND boulder.status = 'active' 
                      AND wall.id = :id 
                      GROUP BY wall.id;";

        $query = $this->getEntityManager()->getConnection()->prepare($statement);

        $query->execute([
            "id" => $id,
            "locationId" => $locationId,
        ]);

        return $query->fetchAllAssociative()[0];
    }
}