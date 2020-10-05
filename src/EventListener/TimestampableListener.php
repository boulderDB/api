<?php

namespace App\EventListener;

use App\Entity\TimestampableInterface;
use App\Service\ContextService;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class TimestampableListener implements EventSubscriber
{
    private ContextService $contextService;

    public function __construct(ContextService $contextService)
    {
        $this->contextService = $contextService;
    }

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

        if ($subject instanceof TimestampableInterface) {
            $subject->setCreatedAt(new \DateTime());
        }
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $subject = $args->getObject();

        if ($subject instanceof TimestampableInterface) {
            $subject->setUpdatedAt(new \DateTime());
        }
    }
}