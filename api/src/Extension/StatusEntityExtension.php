<?php

namespace App\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Enum\EntityStatus;
use App\Entity\IStatusEntity;
use Doctrine\ORM\QueryBuilder;

final class StatusEntityExtension extends AbstractExtension implements QueryCollectionExtensionInterface,
                                                                       QueryItemExtensionInterface
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

        $this->removeDeleted($queryBuilder, $queryNameGenerator);
    }

    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        Operation $operation = null,
        array $context = []
    ): void {
        if (!$this->supports($resourceClass, $operation, $context)) {
            return;
        }

        $this->removeDeleted($queryBuilder, $queryNameGenerator);
    }


    private function removeDeleted(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator): void
    {
        $rootAlias = $queryBuilder->getRootAliases()[0];

        $statusDeleted = $queryNameGenerator->generateParameterName("status_deleted");

        $queryBuilder->andWhere("$rootAlias.status != :$statusDeleted");
        $queryBuilder->setParameter($statusDeleted, EntityStatus::DELETED);
    }

    private function supports(string $resourceClass, Operation $operation, array $context = []): bool
    {
        return self::implements($resourceClass, IStatusEntity::class);
    }

}
