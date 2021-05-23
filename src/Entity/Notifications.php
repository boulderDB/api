<?php

namespace App\Entity;

use App\Service\ContextService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Notifications
{
    private const TYPE_DOUBTS = "doubts";
    private const TYPE_ERRORS = "errors";
    private const TYPE_COMMENTS = "comments";

    private Collection $locations;
    private User $user;

    public function __construct(User $user)
    {
        $this->locations = new ArrayCollection();
        $this->user = $user;
    }

    public function setLocations(array $locations): void
    {
        $this->locations = new ArrayCollection($locations);
    }

    public function addLocation(Location $location): void
    {
        $this->locations->add($location);
    }

    public function getMap(): array
    {
        $notifications = [];

        /**
         * @var \App\Entity\Location $location
         */
        foreach ($this->locations->toArray() as $location) {
            // add default types
            foreach (self::getDefaultTypes() as $type) {
                $notifications[
                    self::getLocationNotification($type, $location->getUrl())
                ] = true;
            }

            $locationAdminRole = ContextService::getLocationRoleName('ADMIN', $location->getId(), true);

            // if is admin, add notifications
            if (in_array($locationAdminRole, $this->user->getRoles(), true)) {
                foreach (self::getAdminTypes() as $notificationType) {
                    $notifications[self::getLocationNotification(
                        $notificationType,
                        $location->getUrl()
                    )] = true;
                }
            }
        }

        return $notifications;
    }

    public static function getLocationNotification(string $type, string $location): string
    {
        return "$type@$location";
    }

    public static function getAdminTypes(): array
    {
        return [
            self::TYPE_ERRORS,
            self::TYPE_COMMENTS
        ];
    }

    public static function getDefaultTypes(): array
    {
        return [self::TYPE_DOUBTS];
    }
}