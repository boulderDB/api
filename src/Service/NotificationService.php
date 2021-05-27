<?php

namespace App\Service;

use App\Entity\Location;
use App\Entity\Notification;
use App\Entity\User;
use App\Repository\LocationRepository;

class NotificationService
{
    private LocationRepository $locationRepository;

    public function __construct(LocationRepository $locationRepository)
    {
        $this->locationRepository = $locationRepository;
    }

    /**
     * @return Location[]
     */
    public function getUserNotifications(User $user): array
    {
        /**
         * @var \App\Entity\Location[] $locations
         */
        $locations = $this->locationRepository->findAll();
        $notifications = [];

        foreach ($locations as $location) {
            $locationAdminRole = ContextService::getLocationRoleName('ADMIN', $location->getId(), true);

            foreach (Notification::getDefaultTypes() as $type) {
                $notifications[] = self::createNotification($user, $location, $type);
            }

            // if is admin, add notifications
            if (in_array($locationAdminRole, $user->getRoles(), true)) {
                foreach (Notification::getAdminTypes() as $type) {
                    $notifications[] = self::createNotification($user, $location, $type);
                }
            }
        }

        return $notifications;
    }

    private static function createNotification(User $user, Location $location, string $type): Notification
    {
        $notification = new Notification();
        $notification->setUser($user);
        $notification->setLocation($location);
        $notification->setType($type);

        return $notification;
    }
}