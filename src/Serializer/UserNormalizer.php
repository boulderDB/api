<?php

namespace App\Serializer;

use App\Entity\User;
use App\Service\ContextService;
use Symfony\Component\Serializer\Exception\CircularReferenceException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserNormalizer implements ContextAwareNormalizerInterface
{
    private ObjectNormalizer $normalizer;
    private ContextService $contextService;

    public function __construct(ObjectNormalizer $normalizer, ContextService $contextService)
    {
        $this->normalizer = $normalizer;
        $this->contextService = $contextService;
    }

    public function normalize($data, string $format = null, array $context = [])
    {
        $location = $this->contextService->getLocation();

        if (!$location) {
            return $data;
        }

        $data = $this->normalizer->normalize($data, $format, $context);

        $data["roles"] = array_map(function ($role) {
            return ContextService::getPlainRoleName($role);
        }, ContextService::filterLocationRoles($data["roles"], $location->getId()));

        $data["roles"] = array_values($data["roles"]);
        $data["roles"] = array_unique($data["roles"]);

        return $data;
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof User;
    }
}