<?php

namespace App\Repository;

use App\Entity\TimeSlot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Persistence\ManagerRegistry;

class TimeSlotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimeSlot::class);
    }

    public function findExact(string $locationId, string $roomId, string $dayName, string $startTime, string $endTime)
    {
        $statement = "SELECT capacity, allow_quantity FROM timeslot INNER JOIN room ON timeslot.room_id = room.id WHERE tenant_id = :locationId AND day_name = :dayName AND start_time = :startTime AND end_time = :endTime  AND room_id = :roomId";
        $query = $this->getEntityManager()->getConnection()->prepare($statement);

        $query->execute([
            "locationId" => $locationId,
            "dayName" => $dayName,
            "roomId" => $roomId,
            "startTime" => $startTime,
            "endTime" => $endTime,
        ]);

        $result = $query->fetch();

        if (!$result) {
            throw new EntityNotFoundException("Slot for location '$locationId' on '$dayName' from '$startTime' to '$endTime' not found");
        }

        return $result;
    }

    public function findByLocation(int $locationId, string $dayName): array
    {
        $statement = "SELECT timeslot.day_name, timeslot.tart_time, timeslot.end_time, timeslot.capacity, room.id, room.name FROM timeslot INNER JOIN room ON timeslot.room_id = room.id WHERE tenant_id = :locationId AND day_name = :dayName";
        $query = $this->getEntityManager()->getConnection()->prepare($statement);

        $query->execute([
            "locationId" => $locationId,
            "dayName" => $dayName,
        ]);

        return $result = $query->fetchAll();
    }

    public function findByLocationAndRoom(int $locationId, int $roomId, string $dayName): array
    {
        $statement = "SELECT day_name, start_time, end_time, capacity, allow_quantity FROM timeslot INNER JOIN room ON timeslot.room_id = room.id WHERE tenant_id = :locationId AND day_name = :dayName AND room_id = :roomId";
        $query = $this->getEntityManager()->getConnection()->prepare($statement);

        $query->execute([
            "locationId" => $locationId,
            "dayName" => $dayName,
            "roomId" => $roomId
        ]);

        return $result = $query->fetchAll();
    }

    public function getForRoomAndDayName(int $roomId, string $dayName)
    {
        return $this->createQueryBuilder("timeSlot")
            ->innerJoin("timeSlot.room", "room")
            ->where("timeSlot.dayName = :dayName")
            ->andWhere("timeSlot.room = :roomId")
            ->setParameters([
                "dayName" => strtolower($dayName),
                "roomId" => $roomId,
            ])
            ->getQuery()
            ->getResult();
    }
}