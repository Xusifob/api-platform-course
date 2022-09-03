<?php

namespace App\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Enum\EntityStatus;
use App\Entity\IStatusEntity;
use App\Entity\MediaObject;
use Doctrine\ORM\QueryBuilder;

final class MediaObjectExtension extends AbstractExtension implements QueryCollectionExtensionInterface
{

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        Operation $operation = null,
        array $context = []
    ): void {

        if (!$this->supports($resourceClass, $operation, $context)) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $isThumbnail = $queryNameGenerator->generateParameterName("thumbnail");

        $queryBuilder->andWhere("$rootAlias.isThumbnail != :$isThumbnail");
        $queryBuilder->setParameter($isThumbnail, true);

    }

    private function supports(string $resourceClass, Operation $operation, array $context = []): bool
    {
        return is_subclass_of($resourceClass, MediaObject::class);
    }

}
