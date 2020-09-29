<?php

namespace App\EventListener;

use App\Entity\Location;
use App\Entity\User;
use App\Repository\AscentDoubtRepository;
use App\Repository\LocationRepository;
use BlocBeta\Factory\RedisConnectionFactory;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;

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

        $this->redis = RedisConnectionFactory::create();
    }

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $expiration = new \DateTime("+" . $_ENV["JWT_TOKEN_EXPIRATION"] . "seconds");

        /**
         * @var User $user
         */
        $user = $event->getUser();

        $payload = $event->getData();

        $payload = array_merge($payload, [
            "expiration" => $expiration->getTimestamp(),
            "target" => Request::createFromGlobals()->query->get("target"),
            "location" => null,
            "fullRegistration" => false,
            "user" => [
                "id" => $user->getId(),
                "username" => $user->getUsername(),
                "roles" => $user->getRoles(),
                "visible" => $user->isVisible(),
            ]
        ]);

        if ($user->getLastName() && $user->getLastName()) {
            $payload["fullRegistration"] = true;
        }

        if ($user->getLastVisitedLocation()) {

            /**
             * @var Location $location
             */
            $location = $this->locationRepository->find($user->getLastVisitedLocation());

            $payload["location"] = [
                "id" => $location->getId(),
                "name" => $location->getName(),
                "url" => $location->getUrl(),
                "public" => $location->isPublic(),
            ];
        }

        $event->setData($payload);
    }

    public static function getSubscribedEvents()
    {
        return ["lexik_jwt_authentication.on_authentication_success" => "onAuthenticationSuccess"];
    }
}
