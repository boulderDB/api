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

    public function getVisible(int $locationId, \DateTime $date = null)
    {
        $builder = $this->createQueryBuilder("event")
            ->where("event.location = :locationId")
            ->andWhere("event.visible = true")
            ->setParameter("locationId", $locationId);

        if ($date) {
            $builder
                ->andWhere("event.startDate > :current")
                ->andWhere("event.endDate < :current")
                ->setParameter("current", $date);
        }

        return $builder->getQuery()->getResult();
    }
}