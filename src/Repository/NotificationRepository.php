<?php

namespace App\Repository;

use App\Entity\Notification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    /**
     * @return Notification[]
     */
    public function getByUser(int $userId):array
    {
        return $this->createQueryBuilder('notification')
            ->where("notification.user = :userId")
            ->setParameter("userId", $userId)
            ->getQuery()
            ->getResult();
    }

}