<?php

namespace App\Service;

use App\Entity\Location;
use App\Entity\User;

class NotificationService
{
    public const TYPE_DOUBTS = "doubts";
    public const TYPE_ERRORS = "errors";
    public const TYPE_COMMENTS = "comments";

    public static function getLocationNotification(string $type, string $location): string
    {
        return "$type@$location";
    }

    public static function getAdminNotifications(): array
    {
        return [self::TYPE_DOUBTS];
    }

    public static function getUserNotifications(): array
    {
        return [
            self::TYPE_ERRORS,
            self::TYPE_COMMENTS
        ];
    }

    public static function getNotifications(User $user, Location $location): array
    {
        $notifications = [
            NotificationService::getLocationNotification(
                NotificationService::TYPE_DOUBTS,
                $location->getUrl()
            ) => true
        ];

        $role = ContextService::getLocationRoleName('ADMIN', $location->getId(), true);

        if (in_array($role, $user->getRoles(), true)) {
            $notifications[NotificationService::getLocationNotification(
                NotificationService::TYPE_ERRORS,
                $location->getUrl()
            )] = true;

            $notifications[NotificationService::getLocationNotification(
                NotificationService::TYPE_COMMENTS,
                $location->getUrl()
            )] = true;
        }

        return $notifications;
    }
}