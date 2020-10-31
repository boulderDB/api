<?php

namespace App\Repository;

use App\Entity\TimeSlot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TimeSlotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimeSlot::class);
    }

    public function getForLocationAndRoom(int $locationId, int $roomId = null)
    {
        $builder = $this->createQueryBuilder("timeSlot")
            ->innerJoin("timeSlot.room", "room")
            ->where("room.location = :locationId")
            ->setParameter("locationId", $locationId);

        if ($roomId) {
            $builder->andWhere("room.id = :roomId")->setParameter("roomId", $roomId);
        }

        return $builder->getQuery()->getResult();
    }

    public function getForRoomAndDayName(int $roomId, string $dayName)
    {
        return $this->createQueryBuilder("timeSlot")
            ->innerJoin("timeSlot.room", "room")
            ->where("timeSlot.dayName = :dayName")
            ->andWhere("timeSlot.room = :roomId")
            ->andWhere("timeSlot.enabled = true")
            ->orderBy("timeSlot.startTime, timeSlot.endTime", "ASC")
            ->setParameters([
                "dayName" => strtolower($dayName),
                "roomId" => $roomId,
            ])
            ->getQuery()
            ->getResult();
    }
}
