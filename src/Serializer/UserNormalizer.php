<?php

namespace App\Serializer;

use App\Entity\User;
use App\Service\ContextService;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class UserNormalizer implements ContextAwareNormalizerInterface
{
    private ObjectNormalizer $normalizer;

    public function __construct(ObjectNormalizer $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    public function normalize($data, string $format = null, array $context = [])
    {
        /**
         * @var User $user
         */
        $user = $data;
        $data = $this->normalizer->normalize($data, $format, $context);

        $data["roles"] = array_map(function ($role) {
            return ContextService::getPlainRoleName($role);
        }, ContextService::filterLocationRoles($data["roles"], $user->getLastVisitedLocation()->getId()));

        $data["roles"] = array_values($data["roles"]);
        $data["roles"] = array_unique($data["roles"]);

        return $data;
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof User;
    }
}