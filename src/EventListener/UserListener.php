<?php

namespace App\EventListener;

use App\Entity\User;
use App\Service\NotificationService;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class UserListener implements EventSubscriber
{
    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {

        $this->notificationService = $notificationService;
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $subject = $args->getObject();

        if (!$subject instanceof User) {
            return;
        }

        $notifications = $this->notificationService->getUserMap($subject);
        $subject->setNotifications($notifications);
    }
}