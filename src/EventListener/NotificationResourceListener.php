<?php

namespace App\EventListener;

use App\Entity\AscentDoubt;
use App\Entity\BoulderComment;
use App\Entity\BoulderError;
use App\Entity\LocationResourceInterface;
use App\Entity\NotificationResourceInterface;
use App\Factory\RedisConnectionFactory;
use App\Repository\UserRepository;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class NotificationResourceListener implements EventSubscriber
{
    private \Redis $redis;
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->redis = RedisConnectionFactory::create();
        $this->userRepository = $userRepository;
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
        ];
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $subject = $args->getObject();

        if (!$subject instanceof NotificationResourceInterface) {
            return;
        }

        if (!$subject instanceof LocationResourceInterface) {
            return;
        }

        $type = $subject->getType();
        $locationId = $subject->getLocation()->getId();
        $admins = $this->userRepository->getLocationAdmins($locationId);

        if ($subject instanceof BoulderError) {
            $this->queueAdminNotifications($admins, $type, [
                "location" => $subject->getLocation()->getId(),
                "boulder" => $subject->getBoulder()->getId(),
                "boulderError" => $subject->getId()
            ]);
        }

        if ($subject instanceof BoulderComment) {
            $this->queueAdminNotifications($admins, $type, [
                "location" => $subject->getLocation()->getId(),
                "boulder" => $subject->getBoulder()->getId(),
                "boulderComment" => $subject->getId()
            ]);
        }

        if ($subject instanceof AscentDoubt) {
            $userId = $subject->getRecipient()->getId();

            $this->redis->set("notification:$type:user:$userId", json_encode([
                "location" => $subject->getLocation()->getId(),
                "ascent" => $subject->getId()
            ]));

        }
    }

    private function queueAdminNotifications(array $admins, string $type, array $data = []): void
    {
        foreach ($admins as $admin) {
            $userId = $admin->getId();

            if (!$admin->hasNotification($type)) {
                continue;
            }

            $this->redis->set("notification:$type:user:$userId", json_encode($data));
        }
    }

}