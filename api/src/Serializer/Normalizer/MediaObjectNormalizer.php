<?php

namespace App\Serializer\Normalizer;

use ArrayObject;
use App\Entity\IEntity;
use App\Entity\MediaObject;
use App\Service\MediaUploader;
use Aws\S3\S3Client;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;


final class MediaObjectNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'MEDIA_OBJECT_NORMALIZER_ALREADY_CALLED';

    public function __construct(private readonly MediaUploader $uploader)
    {
    }

    /**
     * @param MediaObject $object
     * @throws ExceptionInterface
     */
    public function normalize(
        $object,
        ?string $format = null,
        array $context = []
    ): array|string|int|float|bool|ArrayObject|null {
        $context[self::buildCalledKey($object)] = true;

        $object->previewUrl = $this->uploader->getS3SignedUrl($object);

        return $this->normalizer->normalize($object, $format, $context);
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        if (!($data instanceof MediaObject)) {
            return false;
        }
        // Make sure we're not called twice
        return !isset($context[self::buildCalledKey($data)]);
    }


    private function buildCalledKey(MediaObject $entity): string
    {
        return sprintf('%s_%s', self::ALREADY_CALLED, $entity->getId());
    }


}
