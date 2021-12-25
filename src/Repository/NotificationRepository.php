<?php

namespace App\Repository;

use App\Entity\Notification;
use App\Entity\User;
use App\Service\ContextService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    public function findUserNotifications(User $user)
    {
        $notifications = $this->findAll();

        $userNotificationChecksums = [];

        foreach ($user->getRoles() as $role) {
            if (!ContextService::getLocationIdFromRoleName($role)) {
                continue;
            }

            foreach (Notification::TYPES as $type) {
                $userNotificationChecksums[] = Notification::getChecksum(
                    ContextService::getLocationIdFromRoleName($role),
                    $type,
                    ContextService::getPlainRoleName($role)
                );
            }
        }

        $matches = [];

        /**
         * @var Notification $notification
         */
        foreach ($notifications as $notification) {
            if (!$notification->getRoles()) {
                $matches[] = $notification;
                continue;
            }

            foreach ($notification->getRoles() as $role) {
                /**
                 * @var Notification $notification
                 */
                $checksum = Notification::getChecksum(
                    $notification->getLocation()->getId(),
                    $notification->getType(),
                    $role
                );

                if (in_array($checksum, $userNotificationChecksums)) {
                    $matches[] = $notification;
                }
            }
        }

        return $matches;
    }
}