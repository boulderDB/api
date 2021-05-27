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
use App\Service\Serializer;
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

        $id = $subject->getId();
        $type = $subject->getType();
        $locationId = $subject->getLocation()->getId();
        $admins = $this->userRepository->getLocationAdmins($subject->getLocation()->getId());

        if ($subject instanceof BoulderError) {
            $this->queueAdminNotifications($admins, $locationId, $type, [
                "location" => Serializer::serialize($subject->getLocation()),
                "boulder" => Serializer::serialize($subject->getBoulder()),
                "boulderError" => Serializer::serialize($subject),
                "type" => $subject->getType(),
                "link" => $_ENV["CLIENT_HOSTNAME"] . "/" . $subject->getLocation()->getUrl() . "/admin/errors?focus=$id"
            ]);
        }

        if ($subject instanceof BoulderComment) {
            $this->queueAdminNotifications($admins, $locationId, $type, [
                "location" => Serializer::serialize($subject->getLocation()),
                "boulder" => Serializer::serialize($subject->getBoulder()),
                "boulderComment" => Serializer::serialize($subject),
                "type" => $subject->getType(),
                "link" => $_ENV["CLIENT_HOSTNAME"] . "/" . $subject->getLocation()->getUrl() . "/admin/comments?focus=$id"
            ]);
        }

        if ($subject instanceof AscentDoubt) {
            $userId = $subject->getRecipient()->getId();

            $this->redis->set("notification:$type:user:$userId", json_encode([
                "location" => Serializer::serialize($subject->getLocation()),
                "boulder" => Serializer::serialize($subject->getBoulder()),
                "ascent" => Serializer::serialize($subject->getAscent()),
                "user" => Serializer::serialize($subject->getRecipient()),
                "type" => $subject->getType(),
                "link" => $_ENV["CLIENT_HOSTNAME"] . "/" . $subject->getLocation()->getUrl() . "/doubts"
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