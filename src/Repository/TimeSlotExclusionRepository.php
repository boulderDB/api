<?php

namespace App\Repository;

use App\Entity\TimeSlotExclusion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TimeSlotExclusionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimeSlotExclusion::class);
    }

    public function pendingLocationExclusions(string $locationId): array
    {
        $current = new \DateTimeImmutable();

        $statement = "SELECT time_slot_exclusion.id, start_date, end_date, quantity, note, room_id FROM time_slot_exclusion INNER JOIN room ON time_slot_exclusion.room_id = room.id WHERE tenant_id = :locationId AND time_slot_exclusion.start_date > :date";
        $query = $this->getEntityManager()->getConnection()->prepare($statement);

        $query->execute([
            "locationId" => $locationId,
            "date" => $current->format("Y-m-d H:i:s")
        ]);

        return $query->fetchAllAssociative();
    }

    public function getPendingForRoomAndDate(int $roomId, \DateTimeInterface $date)
    {
        return $this->createQueryBuilder("exclusion")
            ->where("exclusion.room = :roomId")
            ->andWhere("exclusion.startDate >= :start")
            ->setParameters([
                "roomId" => $roomId,
                "start" => $date
            ])
            ->getQuery()
            ->getResult();
    }

}