<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EventRepository extends ServiceEntityRepository
{
    use FilterTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function getActive(int $locationId, \DateTime $date = null)
    {
        $builder = $this->createQueryBuilder("event")
            ->where("event.location = :locationId")
            ->andWhere("event.active = true")
            ->andWhere("event.start > :current")
            ->andWhere("event.end < :current")
            ->setParameter("locationId", $locationId);

        if ($date) {
            $builder
                ->andWhere("event.start > :current")
                ->andWhere("event.end < :current")
                ->setParameter("current", $date);
        }

        return $builder->getQuery()->getResult();
    }
}