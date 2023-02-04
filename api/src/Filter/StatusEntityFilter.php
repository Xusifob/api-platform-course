<?php

declare(strict_types=1);

namespace App\Filter;


use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Enum\EntityStatus;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\PropertyInfo\Type;

class StatusEntityFilter extends AbstractFilter
{

    protected function filterProperty(
        string $property,
        mixed $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        Operation $operation = null,
        array $context = []
    ) : void {

        if ($property === "archived") {
            $this->addArchivedFilter($queryBuilder, $queryNameGenerator, $value);
            return;
        }
    }


    private function addArchivedFilter(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        mixed $value
    ): void {
        $value = self::getBoolean($value);

        if (null === $value) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $paramName = $queryNameGenerator->generateParameterName("entity_status");

        $queryBuilder->andWhere("$rootAlias.status = :$paramName");
        $queryBuilder->setParameter($paramName, $value ? EntityStatus::ARCHIVED : EntityStatus::ACTIVE);
    }


    public function getDescription(string $resourceClass): array
    {
        $description = [];
        if (!$this->properties) {
            return [];
        }

        $description["archived"] = [
            'property' => "archived",
            'type' => Type::BUILTIN_TYPE_BOOL,
            'required' => false,
            'description' => 'Filter the entity, only display the archived entities',
            'openapi' => [
                'example' => false,
                'allowEmptyValue' => false,
            ],
        ];


        return $description;
    }


    public static function getBoolean(mixed $value): ?bool
    {
        if (null === $value) {
            return null;
        }

        if($value === "") {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, [FILTER_NULL_ON_FAILURE, FILTER_FLAG_EMPTY_STRING_NULL]);
    }


}
