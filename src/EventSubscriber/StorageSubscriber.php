<?php

namespace App\EventSubscriber;

use App\Entity\Area;
use App\Entity\Boulder;
use App\Entity\Grade;
use App\Entity\HoldStyle;
use App\Entity\Location;
use App\Entity\Tag;
use App\Entity\Wall;
use App\Factory\RedisConnectionFactory;
use App\Service\ContextService;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

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

    /**
     * @var \Redis
     */
    private $redis;
    private $contextService;

    public function __construct(ContextService $contextService)
    {
        $this->contextService = $contextService;
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

        RedisConnectionFactory::create()->set(static::getStorageKey($this->getLocationId()), $this->generateHash());
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        if (!self::isStorageSubject($args->getObject())) {
            return;
        }

        RedisConnectionFactory::create()->set(static::getStorageKey($this->getLocationId()), $this->generateHash());
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        if (!self::isStorageSubject($args->getObject())) {
            return;
        }

        RedisConnectionFactory::create()->set(static::getStorageKey($this->getLocationId()), $this->generateHash());
    }

    private function getLocationId(): int
    {
        return $this->contextService->getLocation()->getId();
    }

    public static function getStorageKey(string $locationId): string
    {
        return "storage_hash_{$locationId}";
    }

    private function generateHash()
    {
        $current = new \DateTime();

        return md5("{$current->getTimestamp()}_{$this->getLocationId()}");
    }

    private static function isStorageSubject($subject): bool
    {
        $subjectClass = get_class($subject);

        return in_array($subjectClass, self::SUBJECTS);
    }
}