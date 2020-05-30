<?php

namespace App\EventSubscriber;

use App\Components\Entity\LocationResourceInterface;
use App\Components\Entity\TimestampableInterface;
use App\Service\ContextService;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\Persistence\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

class LocationResourceSubscriber implements EventSubscriber
{
    private $contextService;

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

        if ($subject instanceof LocationResourceInterface) {
            $subject->setLocation($this->contextService->getLocation());
        }

        if ($subject instanceof TimestampableInterface) {
            $subject->setCreatedAt(new \DateTime());
        }
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $subject = $args->getObject();

        if ($subject instanceof LocationResourceInterface) {
            $subject->setLocation($this->contextService->getLocation());
        }

        if ($subject instanceof TimestampableInterface) {
            $subject->setUpdatedAt(new \DateTime());
        }
    }
}