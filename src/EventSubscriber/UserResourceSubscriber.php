<?php

namespace App\EventSubscriber;

use App\Components\Entity\UserResourceInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class UserResourceSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $subject = $args->getObject();

        if ($subject instanceof UserResourceInterface) {
            $user = $subject->getUser();
            $user->setLastActivity(new \DateTime());

            $args->getObjectManager()->persist($user);
            $args->getObjectManager()->flush();
        }
    }
}