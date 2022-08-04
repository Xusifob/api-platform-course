<?php

namespace App\Serializer\Normalizer;

use App\Entity\Notification;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class NotificationNormalizer implements NormalizerInterface, NormalizerAwareInterface, CacheableSupportsMethodInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'NOTIFICATION_NORMALIZER_ALREADY_CALLED';

    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    /**
     * @param Notification $object
     * @param string|null $format
     * @param array $context
     * @return array
     * @throws ExceptionInterface
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED] = true;

        $type = strtolower($object->type->value);

        $object->title = $this->translator->trans("type.$type.title", [], "notifications");
        $object->content = $this->translator->trans("type.$type.content", [], "notifications");

        return $this->normalizer->normalize($object, $format, $context);
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof Notification;
    }


    public function hasCacheableSupportsMethod(): bool
    {
        return false;
    }


}
