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
        $date = new \DateTime();
        $date->modify("+1hour"); # todo remove hardcoded modifier

        return $this->createQueryBuilder("event")
            ->where("event.location = :locationId")
            ->andWhere("event.visible = true")
            ->andWhere("event.startDate > :current")
            ->andWhere("event.endDate < :current")
            ->setParameter("current", $date)
            ->setParameter("locationId", $locationId)
            ->getQuery()->getResult();
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