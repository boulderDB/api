<?php

namespace App\EventListener;

use App\Controller\BoulderController;
use App\Entity\Boulder;
use App\Factory\RedisConnectionFactory;
use App\Repository\BoulderRepository;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class BoulderListener implements EventSubscriber
{
    private \Redis $redis;

    public function __construct()
    {
        $this->redis = RedisConnectionFactory::create();
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

        $this->redis->del(BoulderRepository::BOULDER_QUERY_CACHE_KEY);

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