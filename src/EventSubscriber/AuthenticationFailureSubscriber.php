<?php

namespace App\EventSubscriber;

use App\Components\Controller\ApiControllerTrait;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;

class AuthenticationFailureSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            Events::AUTHENTICATION_FAILURE => 'onAuthenticationFailure'
        ];
    }

    public function onAuthenticationFailure(AuthenticationFailureEvent $event)
    {
        ApiControllerTrait::rateLimit(Request::createFromGlobals(), 'login', 10);
    }
}