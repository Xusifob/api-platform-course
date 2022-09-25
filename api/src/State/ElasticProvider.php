<?php

namespace App\State;

use ApiPlatform\Elasticsearch\Extension\RequestBodySearchCollectionExtensionInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use App\Bridge\Elasticsearch\ElasticService;
use App\Pagination\Paginator;
use Doctrine\ORM\EntityManagerInterface;
use stdClass;


class ElasticProvider implements ProviderInterface
{
    public function __construct(
        private readonly ElasticService $elasticService,
        private readonly EntityManagerInterface $em,
        private readonly Pagination $pagination,
        private readonly iterable $collectionExtensions
    ) {
    }


    /**
     * {@inheritDoc}
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Paginator
    {
        $resourceClass = $operation->getClass();

        $body = [];

        /** @var RequestBodySearchCollectionExtensionInterface $collectionExtension */
        foreach ($this->collectionExtensions as $collectionExtension) {
            $body = $collectionExtension->applyToCollection($body, $resourceClass, $operation, $context);
        }

        $limit = $body['size'] ??= $this->pagination->getLimit($operation, $context);
        $body['from'] ??= $this->pagination->getOffset($operation, $context);

        $page = $this->pagination->getPage($context);

        if (!isset($body['query']) && !isset($body['aggs'])) {
            $body['query'] = ['match_all' => new stdClass()];
        }

        $data = $this->elasticService->search($resourceClass, $body);

        $objects = $this->em->getRepository($resourceClass)->findBy(['id' => $data['ids']]);

        return new Paginator($objects, $data['totalItems'], $page, $limit);
    }


}
