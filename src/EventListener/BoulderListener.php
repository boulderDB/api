<?php

namespace App\EventListener;

use App\Entity\Boulder;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class BoulderListener implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $subject = $args->getObject();

        if ($subject instanceof Boulder && !$subject->getInternalGrade()) {
            $subject->setInternalGrade($subject->getGrade());
        }
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $subject = $args->getObject();

        if ($subject instanceof Boulder && !$subject->getInternalGrade()) {
            $subject->setInternalGrade($subject->getGrade());
        }
    }
}