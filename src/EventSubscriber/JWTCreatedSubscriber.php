<?php

namespace App\EventSubscriber;

use App\Entity\Location;
use App\Entity\User;
use App\Factory\RedisConnectionFactory;
use App\Repository\LocationRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class JWTCreatedSubscriber implements EventSubscriberInterface
{
    private $locationRepository;

    public function __construct(LocationRepository $locationRepository)
    {
        $this->locationRepository = $locationRepository;
    }

    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $payload = $event->getData();

        /**
         * @var User $user
         */
        $user = $event->getUser();
        $payload['id'] = $user->getId();

        if ($user->getLastVisitedLocation()) {
            /**
             * @var Location $location
             */
            $location = $this->locationRepository->find($user->getLastVisitedLocation());

            $payload['location'] = [
                'id' => $location->getId(),
                'name' => $location->getName(),
                'url' => $location->getUrl(),
            ];

            $payload['storageVersion'] = self::getLocationStorageHash($location->getId());
        }

        $event->setData($payload);
    }

    public static function getSubscribedEvents()
    {
        return [
            'lexik_jwt_authentication.on_jwt_created' => 'onJWTCreated'
        ];
    }

    private static function getLocationStorageHash(int $locationId): ?string
    {
        $redis = RedisConnectionFactory::create();

        return $redis->get(StorageSubscriber::getStorageKey($locationId));
    }
}