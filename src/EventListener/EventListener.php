<?php

namespace App\EventListener;

use App\Entity\Event;
use App\Scoring\DefaultScoring;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class EventListener implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            Events::postLoad
        ];
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $subject = $args->getObject();

        if (!$subject instanceof Event) {
            return;
        }

        $scoring = new DefaultScoring();

        foreach ($subject->getBoulders() as $boulder) {
            $scoring->calculateScore($boulder, $subject);
        }
    }
}