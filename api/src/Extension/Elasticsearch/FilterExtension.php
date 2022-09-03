<?php

namespace App\Extension\Elasticsearch;

use ApiPlatform\Elasticsearch\Extension\RequestBodySearchCollectionExtensionInterface;
use ApiPlatform\Metadata\Operation;
use App\Filter\Elasticsearch\FilterInterface;
use Psr\Container\ContainerInterface;

class FilterExtension implements RequestBodySearchCollectionExtensionInterface
{

    public function __construct(private readonly ContainerInterface $filterLocator)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function applyToCollection(
        array $requestBody,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): array {
        $resourceFilters = $operation?->getFilters();

        if (!$resourceFilters) {
            return $requestBody;
        }

        $context['filters'] = $context['filters'] ?? [];
        $requestBody = [];

        foreach ($resourceFilters as $filterId) {
            if ($this->filterLocator->has($filterId) && is_a(
                    $filter = $this->filterLocator->get($filterId),
                    $this->getFilterInterface()
                )) {
                $requestBody = $filter->apply($requestBody, $resourceClass, $operation, $context);
            }
        }

        return $requestBody;
    }

    /**
     * Gets the related filter interface.
     */
    protected function getFilterInterface(): string
    {
        return FilterInterface::class;
    }

}
