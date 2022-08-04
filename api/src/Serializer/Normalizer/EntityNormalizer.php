<?php

namespace App\Serializer\Normalizer;

use App\Entity\IEntity;
use App\Entity\Notification;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EntityNormalizer implements NormalizerInterface, NormalizerAwareInterface, CacheableSupportsMethodInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'APP_ENTITY_NORMALIZER_ALREADY_CALLED';

    public function __construct(
        private readonly AccessDecisionManagerInterface $accessDecisionManager,
        private readonly Security $security
    ) {
    }

    /**
     * @param IEntity $object
     * @param string|null $format
     * @param array $context
     * @return array
     * @throws ExceptionInterface
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        $context[self::buildCalledKey($object)] = true;

        $token = $this->security->getToken();

        foreach ($object->getRightKeys() as $right) {
            // @Todo here add cache inside redis for example
            $vote = $token ? $this->accessDecisionManager->decide($token, [$right], $object) : false;
            $object->setRight(strtolower($right), $vote);
        }

        return $this->normalizer->normalize($object, $format, $context);
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        if (!$data instanceof IEntity) {
            return false;
        }

        // Make sure we're not called twice
        if (isset($context[self::buildCalledKey($data)])) {
            return false;
        }

        return true;
    }


    private function buildCalledKey(IEntity $entity): string
    {
        return sprintf('%s_%s', self::ALREADY_CALLED, $entity->getId());
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return false;
    }

}
