<?php

namespace App\EventListener;

use App\Entity\Boulder;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Scoring\DefaultScoring;
use App\Service\ContextService;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class BoulderListener implements EventSubscriber
{
    private TokenStorageInterface $tokenStorage;
    private RequestStack $requestStack;
    private AuthorizationCheckerInterface $authorizationChecker;
    private ContextService $contextService;
    private UserRepository $userRepository;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        RequestStack $requestStack,
        AuthorizationCheckerInterface $authorizationChecker,
        ContextService $contextService,
        UserRepository $userRepository
    )
    {
        $this->tokenStorage = $tokenStorage;
        $this->requestStack = $requestStack;
        $this->authorizationChecker = $authorizationChecker;
        $this->contextService = $contextService;
        $this->userRepository = $userRepository;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postLoad
        ];
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $subject = $args->getObject();

        if (!$subject instanceof Boulder) {
            return;
        }

        $user = $this->getUser();

        if (!$user) {
            return;
        }

        $subject->setUserAscent($user->getId());
    }

    private function getUser(): ?UserInterface
    {
        $request = $this->requestStack->getCurrentRequest();
        $locationAdmin = $this->authorizationChecker->isGranted($this->contextService->getLocationRole(User::ROLE_ADMIN));
        $userId = $request->query->get("forUser");

        if ($userId && $locationAdmin) {
            /**
             * @var User $user
             */
            $user = $this->userRepository->find($userId);

            if (!$user) {
                throw new HttpException(Response::HTTP_BAD_REQUEST, "User $userId not found");
            }

            if (!$user->isActive()) {
                throw new HttpException(Response::HTTP_BAD_REQUEST, "User $userId is inactive");
            }

            return $user;
        }

        return $this->tokenStorage->getToken()?->getUser();
    }
}