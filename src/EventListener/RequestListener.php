<?php

namespace App\EventListener;

use App\Entity\Location;
use App\Entity\User;
use App\Service\ContextService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class RequestListener implements EventSubscriberInterface
{
    private ContextService $contextService;
    private EntityManagerInterface $entityManager;
    private TokenStorageInterface $tokenStorage;

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
        if (!$this->tokenStorage->getToken()) {
            return true;
        }

        /**
         * @var User $user
         */
        $user = $this->tokenStorage->getToken()->getUser();

        if (!$user instanceof User) {
            return false;
        }

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
            throw new HttpException(Response::HTTP_BAD_REQUEST, "Location '$slug' does not exist");
        }

        $this->contextService->setLocation($location);
    }
}
