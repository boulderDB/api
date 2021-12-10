<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EventRepository extends ServiceEntityRepository implements DeactivatableRepositoryInterface
{
    use FilterableRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function getActive(int $locationId)
    {
        $date = new \DateTime("now", new \DateTimeZone("Europe/Berlin"));

        return $this->createQueryBuilder("event")
            ->where("event.location = :locationId")
            ->andWhere("event.visible = true")
            ->andWhere("event.startDate < :date")
            ->andWhere("event.endDate > :date")
            ->setParameter("date", $date)
            ->setParameter("locationId", $locationId)
            ->getQuery()
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
}