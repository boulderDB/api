<?php

namespace App\EventListener;

use App\Entity\AscentDoubt;
use App\Entity\BoulderComment;
use App\Entity\BoulderError;
use App\Entity\LocationResourceInterface;
use App\Entity\NotificationResourceInterface;
use App\Entity\Notifications;
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
        $location = $subject->getLocation()->getUrl();
        $admins = $this->userRepository->getLocationAdmins($subject->getLocation()->getId());

        if ($subject instanceof BoulderError) {
            $this->queueAdminNotifications($admins, $location, $type, [
                "location" => $location,
                "boulder" => $subject->getBoulder()->getId(),
                "boulderError" => $subject->getId(),
                "type" => $subject->getType()
            ]);
        }

        if ($subject instanceof BoulderComment) {
            $this->queueAdminNotifications($admins, $location, $type, [
                "location" => $location,
                "boulder" => $subject->getBoulder()->getId(),
                "boulderComment" => $subject->getId(),
                "type" => $subject->getType()
            ]);
        }

        if ($subject instanceof AscentDoubt) {
            $userId = $subject->getRecipient()->getId();

            $this->redis->set("notification:$type:user:$userId", json_encode([
                "location" => $location,
                "ascent" => $subject->getId(),
                "user" => $userId,
                "type" => $subject->getType()
            ]));
        }
    }

    private function queueAdminNotifications(array $admins, string $location, string $type, array $data = []): void
    {
        /**
         * @var \App\Entity\User $admin
         */
        foreach ($admins as $admin) {
            $userId = $admin->getId();

            $locationType = Notifications::getNotificationId($type, $location);

            if (!$admin->hasNotification($locationType)) {
                continue;
            }

            $data["user"] = $admin->getId();
            $this->redis->set("notification:$type:user:$userId", json_encode($data));
        }
    }

}