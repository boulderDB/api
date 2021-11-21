<?php

namespace App\EventListener;

use App\Entity\User;
use App\Repository\LocationRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class AuthenticationSuccessSubscriber implements EventSubscriberInterface
{
    private LocationRepository $locationRepository;
    private Serializer $serializer;

    public function __construct(
        LocationRepository $locationRepository,
        SerializerInterface $serializer
    )
    {
        $this->locationRepository = $locationRepository;
        $this->serializer = $serializer;
    }

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        /**
         * @var User $user
         */
        $user = $event->getUser();
        $payload = $event->getData();
        $serializerGroups = ["groups" => ["default", "self"]];
        $expiration = new \DateTime("+" . $_ENV["JWT_TOKEN_EXPIRATION"] . "seconds");

        $lastVisitedLocation = $this->locationRepository->find($user->getLastVisitedLocation());

        $payload = array_merge($payload, [
            "expiration" => $expiration->getTimestamp(),
            "target" => Request::createFromGlobals()->query->get("target"),
            "user" => $this->serializer->normalize($user, null, $serializerGroups),
            "lastVisitedLocation" => $this->serializer->normalize($lastVisitedLocation, null, $serializerGroups)
        ]);

        $event->setData($payload);
    }

    public static function getSubscribedEvents()
    {
        return ["lexik_jwt_authentication.on_authentication_success" => "onAuthenticationSuccess"];
    }
}
