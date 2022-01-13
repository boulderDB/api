<?php

namespace App\Normalizer;

use App\Entity\Event;
use App\Entity\User;
use App\Service\ContextService;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class EventNormalizer implements ContextAwareNormalizerInterface
{
    private ObjectNormalizer $normalizer;
    private TokenStorageInterface $tokenStorage;

    public function __construct(ObjectNormalizer $normalizer, TokenStorageInterface $tokenStorage)
    {
        $this->normalizer = $normalizer;
        $this->tokenStorage = $tokenStorage;
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
       return $data instanceof Event;
    }

    /**
     * @param Event $object
     * @param string|null $format
     * @param array $context
     * @return array|\ArrayObject|bool|float|int|mixed|string|null
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = $this->normalizer->normalize($object, $format, $context);

        /**
         * @var User $user
         */
        $user = $this->tokenStorage->getToken()->getUser();

        $data["isParticipant"] = $object->isParticipant($user);

        return $data;
    }
}