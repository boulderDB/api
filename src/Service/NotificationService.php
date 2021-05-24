<?php

namespace App\Service;

use App\Entity\Notifications;
use App\Entity\User;
use App\Repository\LocationRepository;

class NotificationService
{
    private LocationRepository $locationRepository;

    public function __construct(LocationRepository $locationRepository)
    {
        $this->locationRepository = $locationRepository;
    }

    public function getUserMap(User $user):array
    {
        $notifications = new Notifications($user);
        $notifications->setLocations($this->locationRepository->findAll());

        return $notifications->getMap();
    }


}