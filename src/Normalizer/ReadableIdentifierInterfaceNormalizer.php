<?php

namespace App\Normalizer;

use App\Entity\ReadableIdentifierInterface;
use App\Service\ContextService;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class ReadableIdentifierInterfaceNormalizer implements ContextAwareNormalizerInterface
{
    private ObjectNormalizer $normalizer;
    private ContextService $contextService;

    public function __construct(ObjectNormalizer $normalizer, ContextService $contextService)
    {
        $this->normalizer = $normalizer;
        $this->contextService = $contextService;
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof ReadableIdentifierInterface;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        /**
         * @var ReadableIdentifierInterface $object
         */
        $data = $this->normalizer->normalize($object, $format, $context);

        if ($this->contextService->getSettings()?->readableIdentifiers) {
            $data["readableIdentifier"] = $object->getReadableIdentifier();
        }

        return $data;
    }
}