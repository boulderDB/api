<?php

namespace App\EventListener;

use App\Entity\User;
use App\Repository\UserRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

class AuthenticationSuccessSubscriber implements EventSubscriberInterface
{
    private SerializerInterface $serializer;
    private RequestStack $requestStack;
    private UserRepository $userRepository;

    public function __construct(
        SerializerInterface $serializer,
        RequestStack $requestStack,
        UserRepository $userRepository
    )
    {
        $this->serializer = $serializer;
        $this->requestStack = $requestStack;
        $this->userRepository = $userRepository;
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

        $payload = array_merge($payload, [
            "expiration" => $expiration->getTimestamp(),
            "target" => Request::createFromGlobals()->query->get("target"),
            "user" => $this->serializer->normalize($user, null, $serializerGroups),
            "lastVisitedLocation" => $this->serializer->normalize($user->getLastVisitedLocation(), null, $serializerGroups)
        ]);

        $event->setData($payload);
    }

    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $payload = $event->getData();
        $serializerGroups = ["groups" => ["default", "self"]];

        /**
         * @var User $user
         */
        $user = $this->userRepository->findOneByUsername($payload['username']);

        $payload["user"] = $this->serializer->normalize($user, null, $serializerGroups);
        $payload["lastVisitedLocation"] = $this->serializer->normalize($user->getLastVisitedLocation(), null, $serializerGroups);

        $event->setData($payload);
    }

    public static function getSubscribedEvents()
    {
        return [
            "lexik_jwt_authentication.on_authentication_success" => "onAuthenticationSuccess",
            "lexik_jwt_authentication.on_jwt_created" => "onJWTCreated"
        ];
    }
}
