<?php

namespace App\EventListener;

use App\Entity\LocationResourceInterface;
use App\Service\ContextService;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class LocationResourceListener implements EventSubscriber
{
    private ContextService $contextService;

    public function __construct(ContextService $contextService)
    {
        $this->contextService = $contextService;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $subject = $args->getObject();

        if (!$subject instanceof LocationResourceInterface) {
            return;
        }

        if (!$this->contextService->getLocation()) {
            return;
        }

        $subject->setLocation($this->contextService->getLocation());
    }
}
