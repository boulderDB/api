<?php

namespace App\EventListener;

use App\Entity\AscentDoubt;
use App\Entity\BoulderComment;
use App\Entity\BoulderError;
use App\Entity\LocationResourceInterface;
use App\Entity\NotificationResourceInterface;
use App\Factory\RedisConnectionFactory;
use App\Repository\NotificationRepository;
use App\Repository\UserRepository;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class NotificationResourceListener implements EventSubscriber
{
    private \Redis $redis;
    private UserRepository $userRepository;
    private NotificationRepository $notificationRepository;

    public function __construct(UserRepository $userRepository, NotificationRepository $notificationRepository)
    {
        $this->redis = RedisConnectionFactory::create();
        $this->userRepository = $userRepository;
        $this->notificationRepository = $notificationRepository;
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
        $admins = $this->userRepository->getLocationAdmins($subject->getLocation()->getId());

        if ($subject instanceof BoulderError) {
            $this->queueAdminNotifications($admins, $locationId, $type, [
                "location" => $locationId,
                "boulder" => $subject->getBoulder()->getId(),
                "boulderError" => $subject->getId(),
                "type" => $subject->getType()
            ]);
        }

        if ($subject instanceof BoulderComment) {
            $this->queueAdminNotifications($admins, $locationId, $type, [
                "location" => $locationId,
                "boulder" => $subject->getBoulder()->getId(),
                "boulderComment" => $subject->getId(),
                "type" => $subject->getType()
            ]);
        }

        if ($subject instanceof AscentDoubt) {
            $userId = $subject->getRecipient()->getId();

            $this->redis->set("notification:$type:user:$userId", json_encode([
                "location" => $locationId,
                "ascent" => $subject->getId(),
                "user" => $userId,
                "type" => $subject->getType()
            ]));
        }
    }

    private function queueAdminNotifications(array $admins, int $locationId, string $type, array $data = []): void
    {
        /**
         * @var \App\Entity\User $admin
         */
        foreach ($admins as $admin) {
            $userId = $admin->getId();

            if (!$this->notificationRepository->findOneBy(['user' => $userId, 'type' => $type, 'location' => $locationId])) {
                continue;
            }

            $data["user"] = $admin->getId();
            $this->redis->set("notification:$type:user:$userId", json_encode($data));
        }
    }

}