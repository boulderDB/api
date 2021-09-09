<?php

namespace App\Repository;

use App\Entity\Reservation;
use App\Helper\TimeHelper;
use Carbon\Carbon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    public function getLocationChecksum(string $locationId): string
    {
        $statement = "SELECT reservation.id, reservation.user_id, reservation.hash_id, reservation.appeared FROM reservation INNER JOIN room ON reservation.room_id = room.id WHERE room.tenant_id = :locationId";
        $query = $this->getEntityManager()->getConnection()->prepare($statement);

        $query->execute([
            "locationId" => $locationId,
        ]);

        $dump = implode(array_map(function ($item) {
            return implode("+", $item);
        }, $query->fetchAll()));

        return md5($dump);
    }

    public function findReservations(string $hash, bool $fullResult = false): array
    {
        if ($fullResult) {
            $statement = "SELECT reservation.id, users.first_name, users.last_name, users.email, users.username, reservation.appeared, reservation.quantity FROM reservation INNER JOIN users ON reservation.user_id = users.id WHERE hash_id = :hash";
        } else {
            $statement = "SELECT id, user_id, quantity FROM reservation WHERE hash_id = :hash";
        }

        $query = $this->getEntityManager()->getConnection()->prepare($statement);

        $query->execute([
            "hash" => $hash
        ]);

        return $query->fetchAll();
    }

    public function countHashIds(string $hashId): int
    {
        $statement = "SELECT count(hash_id) FROM reservation WHERE hash_id = :hashId";
        $query = $this->getEntityManager()->getConnection()->prepare($statement);

        $query->execute([
            "hashId" => $hashId
        ]);

        return $query->fetch()["count"];
    }

    public function countPendingReservations(Reservation $reservation)
    {
        $statement = "SELECT count(id) FROM reservation WHERE date >= :dateTime AND user_id = :userId AND room_id = :roomId";
        $query = $this->getEntityManager()->getConnection()->prepare($statement);

        $query->execute([
            "dateTime" => Carbon::now()->startOfDay()->format(TimeHelper::DATE_FORMAT_DATETIME),
            "userId" => $reservation->getOwner()->getId(),
            "roomId" => $reservation->getRoom()->getId()
        ]);

        return $query->fetch()["count"];
    }

    public function hasPendingReservationForDate(Reservation $reservation)
    {
        $statement = "SELECT count(id) FROM reservation WHERE date = :dateTime AND user_id = :userId AND room_id = :roomId";
        $query = $this->getEntityManager()->getConnection()->prepare($statement);

        $query->execute([
            "dateTime" => $reservation->getDate()->format(TimeHelper::DATE_FORMAT_DATETIME),
            "userId" => $reservation->getOwner()->getId(),
            "roomId" => $reservation->getRoom()->getId()
        ]);

        return $query->fetch()["count"];
    }

    public function hasPendingReservationForTimeSlot(Reservation $reservation): ?string
    {
        $statement = "SELECT id FROM reservation WHERE hash_id = :hashId AND user_id = :userId";
        $query = $this->getEntityManager()->getConnection()->prepare($statement);

        $query->execute([
            "hashId" => $reservation->getHashId(),
            "userId" => $reservation->getOwner()->getId(),
        ]);

        $result = $query->fetch();

        return $result ? $result["id"] : null;
    }

    public function countPendingByUser(string $userId, string $locationId)
    {
        $current = Carbon::now();

        $statement = "SELECT count(reservation.id) FROM reservation INNER JOIN room ON reservation.room_id = room.id WHERE user_id = :userId AND room.tenant_id = :locationId AND reservation.date >= :date";
        $query = $this->getEntityManager()->getConnection()->prepare($statement);

        $query->execute([
            "userId" => $userId,
            "locationId" => $locationId,
            "date" => $current->startOfDay()->format(TimeHelper::DATE_FORMAT_DATETIME)
        ]);

        return $query->fetch()["count"];
    }

    public function findPendingByUser(string $userId, string $locationId)
    {
        $current = Carbon::now();

        $statement = "SELECT reservation.id, reservation.date, reservation.start_time, reservation.end_time, reservation.room_id, reservation.quantity FROM reservation INNER JOIN room ON reservation.room_id = room.id WHERE user_id = :userId AND room.tenant_id = :locationId AND reservation.date >= :date";
        $query = $this->getEntityManager()->getConnection()->prepare($statement);

        $query->execute([
            "userId" => $userId,
            "locationId" => $locationId,
            "date" =>  $current->startOfDay()->format(TimeHelper::DATE_FORMAT_DATETIME)
        ]);

        return $query->fetchAll();
    }

    public function findNoShows(): array
    {
        $current = new \DateTime();

        return $this->createQueryBuilder("reservation")
            ->where("reservation.date < :date")
            ->andWhere("reservation.appeared != :appeared")
            ->setParameters([
                "date" => $current,
                "appeared" => true
            ])
            ->getQuery()
            ->getArrayResult();
    }
}