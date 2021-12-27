<?php

namespace App\EventListener;

use App\Entity\DeactivatableInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Common\EventSubscriber;
use App\Entity\ReadableIdentifierInterface;

class ReadableIdentifierListener implements EventSubscriber
{

    public function getSubscribedEvents()
    {
        return [
            Events::preUpdate,
            Events::preRemove
        ];
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $subject = $args->getObject();

        if (!$subject instanceof ReadableIdentifierInterface) {
            return;
        }

        $subject->setReadableIdentifier(null);
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $subject = $args->getObject();

        if (!$subject instanceof ReadableIdentifierInterface) {
            return;
        }

        if ($subject instanceof DeactivatableInterface && !$subject->isActive()) {
            $subject->setReadableIdentifier(null);
        }
    }
}