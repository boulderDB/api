<?php

namespace App\EventListener;

use App\Entity\Ascent;
use App\Entity\BoulderRating;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class ChecksumListener implements EventSubscriber
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

        if ($subject instanceof Ascent || $subject instanceof BoulderRating) {
            $subject->setChecksum();
        }
    }
}