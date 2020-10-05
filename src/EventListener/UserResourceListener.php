<?php

namespace App\EventListener;

use App\Entity\User;
use App\Entity\UserResourceInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class UserResourceListener implements EventSubscriber
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

            /**
             * @var User $user
             */
            $user->setLastActivity(new \DateTime());

            $args->getObjectManager()->persist($user);
            $args->getObjectManager()->flush();
        }
    }
}