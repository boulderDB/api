<?php

namespace App\EventSubscriber;

use App\Entity\Boulder;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

class BoulderSubscriber implements EventSubscriber
{

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
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