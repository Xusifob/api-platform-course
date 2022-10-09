<?php

namespace App\Filter;


use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\PropertyInfo\Type;

class ProductFilter extends AbstractFilter
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
        if ($property === "search") {
            $this->addSearchFilter($queryBuilder, $queryNameGenerator, $value);
            return;
        }
    }


    private function addSearchFilter(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        mixed $value
    ): void {
        if (!$value) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $partial = $queryNameGenerator->generateParameterName("partial_search");
        $full = $queryNameGenerator->generateParameterName("full_search");

        $queryBuilder->andWhere("$rootAlias.name LIKE :$full OR $rootAlias.description LIKE :$partial");
        $queryBuilder->setParameter($partial, "$value%");
        $queryBuilder->setParameter($full, "%$value%");
    }


    public function getDescription(string $resourceClass): array
    {
        $description = [];
        if (!$this->properties) {
            return [];
        }

        $description["search"] = [
            'property' => "search",
            'type' => Type::BUILTIN_TYPE_STRING,
            'required' => false,
            'description' => 'Search the entity by name or description content',
            'openapi' => [
                'example' => "Lorem Ipsum",
                'allowEmptyValue' => false,
            ],
        ];


        return $description;
    }

}
