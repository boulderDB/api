<?php

namespace App\Controller;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @method json($data, int $status = 200, array $headers = [], array $context = []): JsonResponse
 */
trait ResponseTrait
{
    use ContextualizedControllerTrait;

    protected function resourceNotFoundResponse(string $name = null, string $id = null)
    {
        $message = "Resource not found";

        if ($name && $id) {
            $message = "{$name} '$id' not found'";
        }

        if ($name && !$id) {
            $message = "{$name} not found'";
        }

        return $this->json([
            "type" => "notFound",
            "message" => $message,
            "code" => Response::HTTP_NOT_FOUND,
        ], Response::HTTP_NOT_FOUND);
    }

    protected function okResponse($data, array $groups = [])
    {
        $response = $this->json($data, Response::HTTP_OK, [], $this->getSerializerContext($groups));

        $response->setPublic();
        $response->headers->addCacheControlDirective('no-cache', true);

        return $response;
    }


    protected function createdResponse(object $class)
    {
        return $this->json(
            [
                "id" => $class->getId(),
            ],
            Response::HTTP_CREATED
        );
    }

    protected function internalErrorResponse()
    {
        return $this->json([
            "type" => "internalError",
            "message" => "Internal trouble. Someone got work to do.",
            "code" => Response::HTTP_INTERNAL_SERVER_ERROR,
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    protected function logicErrorResponse(string $message)
    {
        return $this->json([
            "type" => "logicError",
            "message" => $message,
            "code" => Response::HTTP_NOT_ACCEPTABLE,
        ], Response::HTTP_NOT_ACCEPTABLE);
    }

    protected function badRequestResponse(string $message)
    {
        return $this->json([
            "type" => "badRequest",
            "message" => $message,
            "code" => Response::HTTP_BAD_REQUEST,
        ], Response::HTTP_BAD_REQUEST);
    }

    protected function unauthorizedResponse(string $message = "Access denied")
    {
        return $this->json([
            "type" => "unauthorized",
            "message" => $message,
            "code" => Response::HTTP_FORBIDDEN,
        ], Response::HTTP_FORBIDDEN);
    }

    protected function badFormRequestResponse(FormInterface $form)
    {
        return $this->json([
            "type" => "formError",
            "message" => "Request payload validation failed",
            "code" => Response::HTTP_BAD_REQUEST,
            "errors" => FormErrorTrait::getFormErrors($form),
        ], Response::HTTP_BAD_REQUEST);
    }

    protected function noContentResponse()
    {
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    protected function conflictResponse(string $message)
    {
        return $this->json([
            "message" => $message,
            "code" => Response::HTTP_CONFLICT
        ], Response::HTTP_CONFLICT);
    }

    private function getSerializerContext(array $groups): array
    {
        $context = [
            "groups" => ["default", ...$groups]
        ];

        if (!property_exists($this, "contextService")) {
            return $context;
        }

        if ($this->isLocationAdmin()) {
            $context["groups"][] = "adminDefault";

            if (in_array("detail", $context["groups"])) {
                $context["groups"][] = "adminDetail";
            }
        }

        if ($this->isLocationSetter()) {
            $context["groups"][] = "setterDefault";

            if (in_array("detail", $context["groups"])) {
                $context["groups"][] = "setterDetail";
            }
        }

        return $context;
    }
}
