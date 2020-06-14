<?php

namespace App\EventSubscriber;

use App\Entity\Location;
use App\Entity\User;
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

        $payload['username'] =  $user->getUsername();
        $payload['id'] =  $user->getId();

        if ($user->getLastVisitedLocation()) {
            /**
             * @var Location $location
             */
            $location = $this->locationRepository->find($user->getLastVisitedLocation());

            $payload['location'] = [
                'id' => $location->getId(),
                'name' => $location->getName(),
                'url' => $location->getUrl(),
                'public' => $location->isPublic(),
                'city' => $location->getCity(),
                'zip' => $location->getZip(),
                'addressLineOne' => $location->getAddressLineOne(),
                'addressLineTwo' => $location->getAddressLineTwo(),
                'countryCode' => $location->getCountryCode(),
                'image' => $location->getImage(),
                'website' => $location->getWebsite(),
                'facebook' => $location->getFacebook(),
                'instagram' => $location->getInstagram(),
                'twitter' => $location->getTwitter(),
            ];
        }

        $event->setData($payload);
    }

    public static function getSubscribedEvents()
    {
        return [
            'lexik_jwt_authentication.on_jwt_created' => 'onJWTCreated'
        ];
    }
}