<?php

namespace App\EventListener;

use App\Entity\Boulder;
use App\Entity\User;
use App\Scoring\DefaultScoring;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class BoulderListener implements EventSubscriber
{
    private TokenStorageInterface $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
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

        $scoring = new DefaultScoring();
        $scoring->calculateScore($subject);

        /**
         * @var User $user
         */
        $user = $this->tokenStorage->getToken()?->getUser();

        if (!$user) {
            return;
        }

        $subject->setUserAscent($user->getId());
    }
}