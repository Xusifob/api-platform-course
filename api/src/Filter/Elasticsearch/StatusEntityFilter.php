<?php

namespace App\Filter\Elasticsearch;


use ApiPlatform\Metadata\Operation;
use Symfony\Component\PropertyInfo\Type;

class StatusEntityFilter implements FilterInterface
{

    public function __construct(private readonly array $properties = [])
    {
    }


    public function apply(
        array $clauseBody,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): array {
        foreach ($context['filters'] as $property => $value) {
            $clauseBody = $this->filterProperty($property, $value, $clauseBody, $resourceClass, $operation, $context);
        }

        return $clauseBody;
    }


    protected function filterProperty(
        string $property,
        mixed $value,
        array $clauseBody,
        string $resourceClass,
        Operation $operation = null,
        array $context = []
    ): array {

        if ($property === "archived") {
            return $this->addArchivedFilter($clauseBody, $value);
        }

        return $clauseBody;
    }


    private function addArchivedFilter(
        array $clauseBody,
        mixed $value
    ): array {
        $value = self::getBoolean($value);

        if (null === $value) {
            return $clauseBody;
        }

        $clauseBody['query']["bool"]["must"] = $clauseBody['query']["bool"]["must"] ?? [];

        $clauseBody['query']["bool"]["must"][] = [
            "term" => [
                "active" => !$value
            ]
        ];

        return $clauseBody;
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

        if ($value === "") {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, [FILTER_NULL_ON_FAILURE, FILTER_FLAG_EMPTY_STRING_NULL]);
    }


}
