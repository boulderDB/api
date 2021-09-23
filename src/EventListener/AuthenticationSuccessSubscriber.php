<?php

namespace App\EventListener;

use App\Entity\Location;
use App\Entity\User;
use App\Repository\LocationRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;

class AuthenticationSuccessSubscriber implements EventSubscriberInterface
{
    private LocationRepository $locationRepository;

    public function __construct(
        LocationRepository $locationRepository,
    )
    {
        $this->locationRepository = $locationRepository;
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
            "targetLocation" => null,
            "location" => null,
            "fullRegistration" => false,
            "user" => [
                "id" => $user->getId(),
                "username" => $user->getUsername(),
                "first_name" => $user->getFirstName(),
                "last_name" => $user->getLastName(),
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

            $payload["targetLocation"] = $location->getUrl();
            $payload["location"] = [
                "id" => $location->getId(),
                "name" => $location->getName(),
                "url" => $location->getUrl(),
            ];
        }

        if ($_ENV["DEVELOPMENT_JWT_TOKEN"] === "true") {
            $payload["developmentToken"] = $payload["token"];
        }

        $event->setData($payload);
    }

    public static function getSubscribedEvents()
    {
        return ["lexik_jwt_authentication.on_authentication_success" => "onAuthenticationSuccess"];
    }
}
