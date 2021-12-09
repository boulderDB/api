<?php

namespace App\EventListener;

use App\Entity\AscentDoubt;
use App\Entity\BoulderComment;
use App\Entity\BoulderError;
use App\Entity\LocationResourceInterface;
use App\Entity\NotificationResourceInterface;
use App\Entity\User;
use App\Factory\RedisConnectionFactory;
use App\Repository\NotificationRepository;
use App\Repository\UserRepository;
use App\Service\ContextService;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class NotificationResourceListener implements EventSubscriber
{
    private \Redis $redis;
    private UserRepository $userRepository;
    private NotificationRepository $notificationRepository;
    private Serializer $serializer;

    public function __construct(
        UserRepository $userRepository,
        NotificationRepository $notificationRepository,
        SerializerInterface $serializer
    )
    {
        $this->redis = RedisConnectionFactory::create();
        $this->userRepository = $userRepository;
        $this->notificationRepository = $notificationRepository;
        $this->serializer = $serializer;
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
        $locationId = $subject->getLocation()?->getId();

        if ($subject instanceof AscentDoubt) {
            $userId = $subject->getRecipient()->getId();

            $payload = $this->serializer->serialize(
                [
                    "location" => $subject->getLocation(),
                    "boulder" => $subject->getBoulder(),
                    "ascent" => $subject->getAscent(),
                    "user" => $subject->getRecipient()->getId(),
                    "type" => $subject->getType(),
                    "link" => $_ENV["CLIENT_HOSTNAME"] . "/" . $subject->getLocation()?->getUrl() . "/doubts"
                ],
                null,
                ["groups" => "default"]
            );

            $this->redis->set("notification:$type:user:$userId", $payload);

            return;
        }

        $admins = $this->userRepository->getByRole(
            ContextService::getLocationRoleName(User::ADMIN, $locationId, true)
        );

        $setters = $this->userRepository->getByRole(
            ContextService::getLocationRoleName(User::SETTER, $locationId, true)
        );

        if ($subject instanceof BoulderError) {
            $payload = $this->serializer->normalize(
                [
                    "location" => $subject->getLocation(),
                    "boulder" => $subject->getBoulder(),
                    "boulderError" => $subject,
                    "type" => $subject->getType(),
                    "link" => $_ENV["CLIENT_HOSTNAME"] . "/" . $subject->getLocation()?->getUrl() . "/admin/errors?focus=$id"
                ],
                "json",
                ["groups" => "default"]
            );

            $this->queueAdminNotifications([...$admins, ...$setters], $locationId, $type, $payload);

            return;
        }

        if ($subject instanceof BoulderComment) {
            $payload = $this->serializer->normalize(
                [
                    "location" => $subject->getLocation(),
                    "boulder" => $subject->getBoulder(),
                    "boulderComment" => $subject,
                    "type" => $subject->getType(),
                    "link" => $_ENV["CLIENT_HOSTNAME"] . "/" . $subject->getLocation()?->getUrl() . "/admin/comments?focus=$id"
                ],
                null,
                ["groups" => "default"]
            );

            $this->queueAdminNotifications([...$admins, ...$setters], $locationId, $type, $payload);
        }
    }

    private function queueAdminNotifications(array $users, int $locationId, string $type, array $data = []): void
    {
        /**
         * @var \App\Entity\User $user
         */
        foreach ($users as $user) {
            $userId = $user->getId();

            if (!$this->notificationRepository->findOneBy(['user' => $userId, 'type' => $type, 'location' => $locationId])) {
                continue;
            }

            $data["user"] = $user->getId();
            $this->redis->set("notification:$type:user:$userId", json_encode($data));
        }
    }

}