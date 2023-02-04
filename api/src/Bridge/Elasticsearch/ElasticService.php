<?php

declare(strict_types=1);

namespace App\Bridge\Elasticsearch;

use App\Entity\IEntity;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Exception;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use App\DataCollector\ElasticCollector;
use App\Entity\IElasticEntity;
use App\Serializer\SerialisationGroupGenerator;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use stdClass;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Yaml\Yaml;

class ElasticService
{

    private const PROCESS = 'elastic';

    private const METHOD_POST = "post";

    private const METHOD_PUT = "put";

    private const METHOD_DELETE = "delete";

    private readonly Client $client;


    private array $searches = [];

    private array $updates = [];

    public bool $enabled;

    public function __construct(
        string $host,
        private readonly string $environment,
        private readonly ResourceNameCollectionFactoryInterface $resourceMetadataCollectionFactory,
        private readonly string $mappingDir,
        private readonly ResourceMetadataCollectionFactoryInterface $metadataFactory,
        private readonly NormalizerInterface $normalizer,
        private readonly ManagerRegistry $managerRegistry,
        private readonly ?LoggerInterface $logger = null,

    ) {

        $builder = ClientBuilder::create();

        $this->client = $builder->create()->setHosts([$host])->build();

        $this->enabled = $this->environment !== "test";

    }


    public function getElasticResources(array $resources = []): array
    {
        if ($resources === []) {
            $resources = $this->resourceMetadataCollectionFactory->create();
        }

        return $this->filterResourceClasses($resources);
    }

    public function filterResourceClasses(iterable $array): array
    {
        return array_values(
            array_filter((array)$array, fn($class) => is_subclass_of($class, IElasticEntity::class))
        );
    }


    public function createIndexes(array $resources = []): void
    {
        $resources = $this->getElasticResources($resources);

        foreach ($resources as $resourceClass) {
            $this->createIndex($resourceClass);
        }
    }

    private function createIndex(string $resourceClass): void
    {
        $index = $this->getIndex($resourceClass);

        $mapping = $this->getMapping($resourceClass);

        $params = [
            'index' => $index,
            'body' => [
                'mappings' => $mapping
            ]
        ];

        // Delete index if already exists and create it
        try {
            $this->client->indices()->delete(['index' => $index]);
        } catch (ClientResponseException $exception) {
            if ($exception->getCode() !== 404) {
                throw $exception;
            }
        }

        $this->client->indices()->create($params);
    }


    public function loadIndexes(array $resources = []): void
    {
        $resources = $this->getElasticResources($resources);

        foreach ($resources as $resource) {
            $this->loadIndex($resource);
        }
    }


    private function loadIndex(string $resourceClass): void
    {
        if (null !== $this->logger) {
            $this->logger->info("Loading index for resource: $resourceClass");
        }

        // Get repository for current resource and pull data from DB
        $manager = $this->managerRegistry->getManagerForClass($resourceClass);
        $repository = $manager->getRepository($resourceClass);

        $data = $repository->findAll();

        if (null !== $this->logger) {
            $this->logger->info(sprintf("%s entities found for resource: %s", count($data), $resourceClass));
        }

        foreach ($data as $object) {
            if (null !== $this->logger) {
                $this->logger->info("Loading $object");
            }

            $this->create($object);
        }
    }

    public function search(string $resourceClass, array $body): array
    {
        $index = $this->getIndex($resourceClass);

        $search = [
            'index' => $index,
            'body' => $body
        ];

        $id = $this->collect(ElasticCollector::COLLECT_SEARCH, $search);

        $response = $this->client->search($search);

        $id = $this->collect(ElasticCollector::COLLECT_SEARCH, ["response" => $response], $id);

        if ($response['hits']['total']['value'] === 0) {
            return [
                "data" => [],
                'ids' => [],
                'totalItems' => 0,
            ];
        }

        $ids = [];

        $objects = [];

        /** @var array $hit */
        foreach ($response['hits']['hits'] as $hit) {
            $ids[] = $hit['_id'];
            $objects[] = $hit['_source'];
        }

        return [
            "data" => $objects,
            "ids" => $ids,
            "totalItems" => $response['hits']['total']['value']
        ];
    }

    public function create(IElasticEntity $item): void
    {
        if(!$this->enabled) {
            return;
        }
        $request = $this->getRequestData($item, self::METHOD_POST);

        $this->client->create($request);
    }

    public function update(IElasticEntity $item): void
    {
        if(!$this->enabled) {
            return;
        }
        $request = $this->getRequestData($item, self::METHOD_PUT);

        $request['body'] = [
            'doc' => $request['body']
        ];

        $this->client->update($request);
    }

    public function delete(IElasticEntity $item): void
    {
        if(!$this->enabled) {
            return;
        }
        $request = $this->getRequestData($item, self::METHOD_DELETE);

        $this->client->delete($request);
    }


    public function emptyIndexes(array $resources = []): void
    {
        $resources = $this->getElasticResources($resources);

        foreach ($resources as $resourceClass) {
            if (null !== $this->logger) {
                $this->logger->info("Emptying index for resource: $resourceClass");
            }
            $this->emptyIndex($resourceClass);
        }
    }


    private function emptyIndex(string $resourceClass): void
    {
        $this->client->deleteByQuery([
            'index' => $this->getIndex($resourceClass),
            'body' => [
                "query" => [
                    "match_all" => new stdClass()
                ]
            ]
        ]);
    }


    private function getRequestData(IElasticEntity $item, string $method): array
    {
        $shortName = $this->getIndex($item, false);
        $index = $this->getIndex($item);

        $request = [
            'index' => $index,
            'id' => (string)$item->getId(),
        ];

        if ($method === self::METHOD_DELETE) {
            $this->collect(ElasticCollector::COLLECT_UPDATES, [...$request, ...['action' => $method]]);
            return $request;
        }

        $groups = SerialisationGroupGenerator::buildGroups(
            process: self::PROCESS,
            shortName: $shortName,
            method: $method,
        );

        $body = $this->normalizer->normalize($item, 'array', [
            'groups' => $groups,
        ]);

        $body['id'] = $item->getId();
        $request['body'] = $body;

        $this->collect(ElasticCollector::COLLECT_UPDATES, [...$request, ...['action' => $method]]);

        return $request;
    }

    public function isElasticEntity(IEntity|string $className): bool
    {
        if (is_string($className)) {
            return is_subclass_of($className, IElasticEntity::class);
        }
        return $className instanceof IElasticEntity;
    }

    private function getIndex(IElasticEntity|string $item, bool $withPrefix = true): string
    {
        $item = $this->getResourceClass($item);
        $resource = $this->metadataFactory->create($item);

        $shortName = SerialisationGroupGenerator::getShortName($resource);

        if (!$withPrefix) {
            return $shortName;
        }

        return "{$this->environment}_$shortName";
    }


    private function getMapping(string $resourceClass): array
    {
        $index = $this->getIndex($resourceClass, false);

        $file = "$this->mappingDir/$index.yaml";

        if (!file_exists($file)) {
            throw new Exception("The mapping file $file does not exist, please create it");
        }

        return Yaml::parseFile($file);
    }

    private function getResourceClass(IElasticEntity|string $item): string
    {
        if ($item instanceof IElasticEntity) {
            $item = $item::class;
        }

        return $item;
    }


    private function collect(string $operation, array $data,?string $id = null): string
    {
        $id ??= uniqid();

        if (!in_array($this->environment, ["dev", "test"])) {
            return $id;
        }

        $this->$operation[$id] = array_merge($this->$operation[$id] ?? [],$data);

        return $id;
    }

    public function getSearches(): array
    {
        return $this->searches;
    }

    public function getUpdates(): array
    {
        return $this->updates;
    }


}
