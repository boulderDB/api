<?php

namespace App\EventListener;

use App\Entity\Boulder;
use App\Factory\RedisConnectionFactory;
use App\Service\ContextService;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class BoulderListener implements EventSubscriber
{
    private \Redis $redis;
    private ContextService $contextService;

    public function __construct(ContextService $contextService)
    {
        $this->redis = RedisConnectionFactory::create();
        $this->contextService = $contextService;
    }

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

        if (!$subject instanceof Boulder) {
            return;
        }

        $this->redis->del("boulder-cache-{$this->contextService->getLocation()->getId()}");

        if (!$subject->getInternalGrade()) {
            $subject->setInternalGrade($subject->getGrade());
        }
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $subject = $args->getObject();

        if (!$subject instanceof Boulder) {
            return;
        }

        if (!$subject->getInternalGrade()) {
            $subject->setInternalGrade($subject->getGrade());
        }
    }
}