<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class ExceptionListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => [
                ['onKernelException', 60]
            ],
        ];
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        if ($_ENV["APP_DEBUG"]) {
            throw $exception;
        }

        $response = new JsonResponse([
            "message" => "Internal trouble. Someone got work to do.",
            "code" => $exception->getCode() ? $exception->getCode() : Response::HTTP_INTERNAL_SERVER_ERROR
        ], Response::HTTP_INTERNAL_SERVER_ERROR);

        if ($exception instanceof AuthenticationException) {

            $response = new JsonResponse([
                "message" => "Unauthorized",
                "code" => Response::HTTP_UNAUTHORIZED
            ], Response::HTTP_UNAUTHORIZED);
        }

        if ($exception instanceof HttpExceptionInterface) {

            $response = new JsonResponse([
                "message" => $exception->getMessage(),
                "code" => $exception->getStatusCode()
            ], $exception->getStatusCode());
        }

        $event->setResponse($response);
    }
}
