<?php

namespace App\EventListener;

use App\Controller\RateLimiterTrait;
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
        RateLimiterTrait::rateLimit(Request::createFromGlobals(), 'login', 10);
    }
}