<?php

namespace App\EventSubscriber;

use App\Entity\Area;
use App\Entity\Boulder;
use App\Entity\Grade;
use App\Entity\HoldStyle;
use App\Entity\Location;
use App\Entity\Tag;
use App\Entity\Wall;
use App\Service\ContextService;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Mercure\PublisherInterface;

class StorageSubscriber implements EventSubscriber
{
    const SUBJECTS = [
        Area::class,
        Boulder::class,
        Grade::class,
        HoldStyle::class,
        Location::class,
        Tag::class,
        Wall::class
    ];

    private $contextService;
    private $publisher;

    public function __construct(
        ContextService $contextService,
        PublisherInterface $publisher
    )
    {
        $this->contextService = $contextService;
        $this->publisher = $publisher;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
            Events::postRemove,
            Events::postUpdate
        ];
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        if (!self::isStorageSubject($args->getObject())) {
            return;
        }

        $this->notifyClients();
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        if (!self::isStorageSubject($args->getObject())) {
            return;
        }

        $this->notifyClients();
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        if (!self::isStorageSubject($args->getObject())) {
            return;
        }

        $this->notifyClients();
    }

    private function notifyClients()
    {

    }

    private static function isStorageSubject($subject): bool
    {
        $subjectClass = get_class($subject);

        return in_array($subjectClass, self::SUBJECTS);
    }
}