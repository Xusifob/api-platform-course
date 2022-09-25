<?php

namespace App\Filter\Elasticsearch;


use ApiPlatform\Elasticsearch\Filter\FilterInterface as BaseFilterInterface;
use ApiPlatform\Metadata\Operation;

interface FilterInterface extends BaseFilterInterface
{

    public function apply(
        array $clauseBody,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): array;


}
