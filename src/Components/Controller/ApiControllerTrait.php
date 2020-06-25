<?php

namespace App\Components\Controller;

use App\Exception\RateLimitException;
use App\Factory\RedisConnectionFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait ApiControllerTrait
{
    /**
     * @throws RateLimitException
     */
    public static function rateLimit(Request $request, string $resource, int $limit = 60, int $timeout = 3600): void
    {
        $ip = $_SERVER["HTTP_X_REAL_IP"] ?? $request->getClientIp();
        $redis = RedisConnectionFactory::create();

        $key = "request_limit_{$resource}_{$ip}";

        if ($redis->exists($key)) {
            $redis->incr($key);
        } else {
            $redis->set($key, 1, $timeout);
        }

        if ($redis->get($key) > $limit) {
            throw new RateLimitException();
        }
    }

    private static function getApiDate(int $timestamp): string
    {
        $date = new \DateTime();
        $date->setTimestamp($timestamp);

        return $date->format("c"); // ISO 8601
    }

    private static function isValidId($id): bool
    {
        return (int)$id > 0;
    }

    private function getFormErrors(FormInterface $form)
    {
        $errors = [];

        foreach ($form->getErrors(true) as $error) {
            $errors[$error->getOrigin()->getName()] = $error->getMessage();
        }

        return $errors;
    }

    private function internalError()
    {
        return $this->json([
            "code" => Response::HTTP_INTERNAL_SERVER_ERROR,
            "message" => "Uh oh, that was a slip."
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    private function notFound(string $resource, string $id)
    {
        return $this->json([
            "code" => Response::HTTP_NOT_FOUND,
            "message" => "$resource '$id' not found"
        ], Response::HTTP_NOT_FOUND);
    }

    private function badRequest(array $formErrors)
    {
        return $this->json([
            "code" => Response::HTTP_BAD_REQUEST,
            "message" => $formErrors
        ], Response::HTTP_BAD_REQUEST);
    }

    private function noContent()
    {
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    private function created(string $id)
    {
        return $this->json(["id" => $id], Response::HTTP_CREATED);
    }
}