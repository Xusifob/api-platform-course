<?php

namespace App\Filter\Bridge;

use ApiPlatform\Api\FilterInterface;
use Symfony\Component\PropertyInfo\Type;

class SearchFilter implements FilterInterface
{

    public function getDescription(string $resourceClass): array
    {
        $description["search"] = [
            'property' => "search",
            'type' => Type::BUILTIN_TYPE_STRING,
            'required' => false,
            'description' => 'Filter the entity, search inside article info',
            'openapi' => [
                'example' => "My query",
                'allowEmptyValue' => true,
            ],
        ];


        return $description;
    }

}