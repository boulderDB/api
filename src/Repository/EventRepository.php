<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EventRepository extends ServiceEntityRepository implements DeactivatableRepositoryInterface, FilterableRepositoryInterface
{
    use FilterableRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function getActive(int $locationId, bool $public = true)
    {
        $date = new \DateTime("now", new \DateTimeZone("Europe/Berlin"));

        $queryBuilder = $this->createQueryBuilder("event")
            ->where("event.location = :locationId")
            ->andWhere("event.visible = true")
            ->andWhere("event.startDate < :date")
            ->andWhere("event.endDate > :date")
            ->setParameter("date", $date)
            ->setParameter("locationId", $locationId);

        if ($public) {
            $queryBuilder->andWhere("event.public = true");
        }

        return $queryBuilder->getQuery()
            ->getResult();
    }

    public function getEnded()
    {
        $date = new \DateTime("now", new \DateTimeZone("Europe/Berlin"));

        $queryBuilder = $this->createQueryBuilder("event")
            ->where("event.endDate < :date")
            ->setParameter("date", $date);

        return $queryBuilder->getQuery()
            ->getResult();
    }

    public function getAll(int $locationId)
    {
        return $this->createQueryBuilder("event")
            ->where("event.location = :locationId")
            ->andWhere("event.visible = true")
            ->setParameter("locationId", $locationId)
            ->getQuery()
            ->getResult();
    }

    public function getUpcoming(int $locationId, bool $public = true)
    {
        $date = new \DateTime("now", new \DateTimeZone("Europe/Berlin"));

        $queryBuilder = $this->createQueryBuilder("event")
            ->where("event.location = :locationId")
            ->andWhere("event.visible = true")
            ->andWhere("event.startDate > :date")
            ->setParameter("date", $date)
            ->setParameter("locationId", $locationId);

        if ($public) {
            $queryBuilder->andWhere("event.public = true");
        }

        return $queryBuilder->getQuery()
            ->getResult();
    }

    public function getParticipating(int $locationId, int $userId)
    {
        $date = new \DateTime("now", new \DateTimeZone("Europe/Berlin"));

        $queryBuilder = $this->createQueryBuilder("event")
            ->innerJoin("event.participants", "participant")
            ->where("event.location = :locationId")
            ->andWhere("event.visible = true")
            ->andWhere("event.startDate < :date")
            ->andWhere("event.endDate > :date")
            ->andWhere("participant.id = :userId")
            ->setParameter("date", $date)
            ->setParameter("locationId", $locationId)
            ->setParameter("userId", $userId);

        return $queryBuilder->getQuery()
            ->getResult();
    }

    public function getEndedByBoulder(string $boulderId)
    {
        $date = new \DateTime("now", new \DateTimeZone("Europe/Berlin"));

        return $this->createQueryBuilder("event")
            ->innerJoin("event.boulders", "boulders")
            ->where("boulders.id = :boulderId")
            ->andWhere("event.endDate < :date")
            ->setParameter("date", $date)
            ->setParameter("boulderId", $boulderId)
            ->getQuery()
            ->getResult();
    }

    public function isEventBoulder(int $boulderId): bool
    {
        $events = $this->createQueryBuilder("event")
            ->select("event.id")
            ->innerJoin("event.boulders", "boulders")
            ->where("boulders.id = :boulderId")
            ->setParameter("boulderId", $boulderId)
            ->getQuery()
            ->getResult();

        return (bool)$events;
    }
}