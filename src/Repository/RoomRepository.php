<?php

namespace App\Repository;

use App\Entity\Room;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RoomRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Room::class);
    }

    public function all(int $locationId): array
    {
        $query = $this->getEntityManager()->getConnection()->prepare("SELECT id, name, instructions FROM room WHERE tenant_id = :locationId");
        $query->execute([
            "locationId" => $locationId
        ]);

        return $query->fetchAll();
    }

    public function exists(int $id): bool
    {
        $statement = "SELECT id FROM room WHERE id = :id";
        $query = $this->getEntityManager()->getConnection()->prepare($statement);

        $query->execute([
            "id" => $id
        ]);

        $result = $query->fetch();

        return $result ? true : false;
    }
}
