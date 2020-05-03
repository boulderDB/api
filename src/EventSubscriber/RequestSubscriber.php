<?php

namespace App\EventSubscriber;

use App\Entity\Location;
use App\Entity\User;
use App\Service\ContextService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class RequestSubscriber implements EventSubscriberInterface
{
    private $contextService;
    private $entityManager;
    private $tokenStorage;

    public function __construct(
        ContextService $contextService,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage
    )
    {
        $this->contextService = $contextService;
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.request' => 'onKernelRequest',
        ];
    }

    private function accountDisabled(): bool
    {
        /**
         * @var User $user
         */
        $user = $this->tokenStorage->getToken()->getUser();

        return !$user->isActive();
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        // disable write actions for disabled accounts
        if ($this->accountDisabled() && in_array(strtoupper($request->getMethod()), ["POST", "PUT", "DELETE"])) {
            throw new AccessDeniedException("Your account is disabled");
        }

        $slug = $request->get('location');

        if (!$slug) {
            return;
        }

        $location = $this->entityManager->createQueryBuilder()
            ->select('location')
            ->from(Location::class, 'location')
            ->where('location.url = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$location) {
            throw new \InvalidArgumentException("Location '$slug' does not exist", Response::HTTP_NOT_FOUND);
        }

        $this->contextService->setLocation($location);
    }
}
