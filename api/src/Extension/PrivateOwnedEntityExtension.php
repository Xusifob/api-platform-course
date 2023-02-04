<?php

declare(strict_types=1);

namespace App\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\IOwnedEntity;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

final class PrivateOwnedEntityExtension extends AbstractExtension implements QueryCollectionExtensionInterface,
                                                                      QueryItemExtensionInterface
{


    public function __construct(
        private readonly Security $security,
    ) {
    }


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

        $this->filterMine($queryBuilder, $queryNameGenerator);
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

        $this->filterMine($queryBuilder, $queryNameGenerator);
    }


    private function filterMine(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator): void
    {
        $user = $this->security->getUser();

        $rootAlias = $queryBuilder->getRootAliases()[0];

        if (!($user instanceof User)) {
            $queryBuilder->andWhere("1 = 2");
            return;
        }

        $ownerParameter = $queryNameGenerator->generateParameterName("owner");
        $queryBuilder->andWhere("$rootAlias.owner = :$ownerParameter");
        $queryBuilder->setParameter($ownerParameter, $user->getId());
    }


    private function supports(string $resourceClass, Operation $operation, array $context = []): bool
    {
        if(!is_subclass_of($resourceClass, IOwnedEntity::class)) {
            return false;
        }

        return $resourceClass::isPrivate();

    }

}
