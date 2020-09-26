<?php

namespace App\EventListener;

use App\Entity\AscentDoubt;
use App\Entity\Location;
use App\Entity\User;
use App\Repository\AscentDoubtRepository;
use App\Repository\LocationRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AuthenticationSuccessSubscriber implements EventSubscriberInterface
{
    private LocationRepository $locationRepository;
    private AscentDoubtRepository $ascentDoubtRepository;
    private \Redis $redis;

    public function __construct(
        LocationRepository $locationRepository,
        AscentDoubtRepository $ascentDoubtRepository
    )
    {
        $this->locationRepository = $locationRepository;
        $this->ascentDoubtRepository = $ascentDoubtRepository;

        $this->redis = new \Redis();
        $this->redis->connect($_ENV["REDIS_HOST"]);
    }

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $expiration = new \DateTime("+" . $_ENV["JWT_TOKEN_EXPIRATION"] . "seconds");
        $payload = $event->getData();

        $payload["expiration"] = $expiration->getTimestamp();

        /**
         * @var User $user
         */
        $user = $event->getUser();

        $payload["user"] = [
            "id" => $user->getId(),
            "username" => $user->getUsername(),
            "roles" => $user->getRoles(),
            "visible" => $user->isVisible(),
            "actions" => $this->redis->sMembers("user:{$user->getId()}:actions")
        ];

        $payload["location"] = null;

        if ($user->getLastVisitedLocation()) {
            /**
             * @var Location $location
             */
            $location = $this->locationRepository->find($user->getLastVisitedLocation());

            $payload["location"] = [
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

            $payload['doubts'] = $this->ascentDoubtRepository->getDoubts(
                $location->getId(),
                $user->getId(),
                AscentDoubt::STATUS_UNREAD
            );
        }

        $event->setData($payload);
    }

    public static function getSubscribedEvents()
    {
        return ["lexik_jwt_authentication.on_authentication_success" => "onAuthenticationSuccess"];
    }
}
