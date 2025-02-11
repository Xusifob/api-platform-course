<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use ApiPlatform\Api\IriConverterInterface;
use ApiPlatform\Exception\ResourceClassNotFoundException;
use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Bridge\Elasticsearch\ElasticService;
use App\Entity\IEntity;
use App\Entity\MediaObject;
use App\Entity\User;
use App\Service\MediaUploader;
use App\Tests\Shared\TesterTrait;
use Exception;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Bundle\MercureBundle\DataCollector\MercureDataCollector;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Profiler\Profile;
use Symfony\Component\Mercure\Update;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class ApiTester extends ApiTestCase
{

    use TesterTrait;
    use ReloadDatabaseTrait;


    private Client $apiClient;

    public const FORMAT_JSONLD = "jsonld";

    public const FORMAT_JSONAPI = "jsonapi";

    public const FORMAT_GRAPHQL = "graphql";

    public const FORMAT_VALUES = [
        self::FORMAT_JSONLD,
        self::FORMAT_JSONAPI
    ];

    protected string $format = self::FORMAT_JSONLD;

    // JWT TOKEN
    protected ?string $token = null;

    protected TranslatorInterface $translator;

    protected ResourceMetadataCollectionFactoryInterface $metadataFactory;


    public function setUp(): void
    {
        $this->apiClient = self::createClient();

        $this->translator = self::getContainer()->get(TranslatorInterface::class);
        $this->metadataFactory = self::getContainer()->get(ResourceMetadataCollectionFactoryInterface::class);
        $this->em = self::getContainer()->get('doctrine')->getManager();
    }


    public function tearDown(): void
    {
        parent::ensureKernelShutdown();
        parent::tearDown();
        $this->em->close();
        $this->em = null;
    }


    #[ArrayShape(["string" => "array"])]
    protected function getFormats(): array
    {
        $formats = [];

        foreach (self::FORMAT_VALUES as $format) {
            $formats[$format] = [$format];
        }

        return $formats;
    }


    protected function uploadFile(string $url, string $file, array $params = []): array
    {
        $file = new UploadedFile($file, basename($file));

        $response = $this->apiClient->request(HttpOperation::METHOD_POST, $url, [
            'headers' => $this->getHeaders(['Content-Type' => 'multipart/form-data']),
            'extra' => [
                'parameters' => $params,
                'files' => [
                    'file' => $file,
                ],
            ]
        ]);

        return json_decode($response->getContent(false), true);
    }


    protected function graphQL(string $query): array
    {
        $this->format = self::FORMAT_GRAPHQL;

        return $this->post('/graphql', [
            "operationName" => null,
            "variables" => new \stdClass(),
            'query' => trim($query),
        ]);
    }


    protected function post(string $url, array $json = []): ?array
    {
        return $this->doRequest(HttpOperation::METHOD_POST, $url, $json);
    }

    protected function patch(IEntity|string $url, array $json = []): ?array
    {
        $url = $this->getUrl($url);

        return $this->doRequest(HttpOperation::METHOD_PATCH, $url, $json);
    }

    protected function delete(IEntity|string $url): ?array
    {
        $url = $this->getUrl($url);

        return $this->doRequest(HttpOperation::METHOD_DELETE, $url);
    }


    protected function get(IEntity|string $url, array $query = [], array $options = []): ?array
    {
        $url = $this->getUrl($url);

        return $this->doRequest(HttpOperation::METHOD_GET, $url, $query, $options);
    }


    private function getUrl(IEntity|string $url): string
    {
        if (is_string($url)) {
            return $url;
        }

        $entityUrl = $this->getEntityUri($url);

        if (is_string($entityUrl)) {
            return $entityUrl;
        }

        throw new Exception("No url found for entity $url");
    }

    private function doRequest(string $method, string $url, array $data = [], array $options = []): ?array
    {
        $params = [
            "headers" => $this->getHeaders($options['headers'] ?? [], $method)
        ];

        if ($method === HttpOperation::METHOD_GET) {
            $params['query'] = $data;
        } else {
            $params['json'] = $data;
        }


        try {
            $response = $this->apiClient->request($method, $url, $params);

            return json_decode($response->getContent(), true);
        } catch (HttpException|ClientException $exception) {
            return json_decode($exception->getResponse()->getContent(false), true);
        }
    }


    public function assertArrayHasKeys(array $keys, array $array): void
    {
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $array);
        }
    }


    public function assertResponseHasPostData(array $data, array $postData): void
    {
        match ($this->format) {
            self::FORMAT_JSONLD => $this->assertJsonLdResponsePostData($data, $postData),
            self::FORMAT_JSONAPI => $this->assertJsonApiResponsePostData($data, $postData),
        };
    }

    public function assertGetCollectionCount(int $count, array $data): void
    {
        match ($this->format) {
            self::FORMAT_JSONLD => $this->assertCount($count, $data['hydra:member']),
            self::FORMAT_JSONAPI => $this->assertCount($count, $data['data']),
        };
    }


    public function assertResponseIsNotFound()
    {
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function assertResponseIsBadRequest()
    {
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function assertResponseForbidden()
    {
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function assertResponseIsUnauthorized()
    {
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }


    private function assertJsonLdResponsePostData(array $data, array $postData): void
    {
        $context = $this->getShortName();

        $this->assertEquals("/contexts/{$context}", $data['@context']);
        $this->assertEquals($context, $data["@type"]);
        $this->assertArrayHasKey("@id", $data);

        foreach ($postData as $key => $postDatum) {
            $this->assertEquals($postDatum, $data[$key]);
        }
    }


    private function assertJsonApiResponsePostData(array $data, array $postData): void
    {
        $data = $data['data'];

        $context = $this->getShortName();

        $this->assertEquals($context, $data["type"]);
        $this->assertArrayHasKey("id", $data);

        $attributes = $postData['data']['attributes'] ?? $postData;

        foreach ($attributes as $key => $postDatum) {
            $this->assertEquals($postDatum, $data["attributes"][$key]);
        }
    }


    /**
     * @param array $data
     * @param array $expectedPropertyPaths
     * @param array $expectedMessages
     * @return void
     */
    public function assertHasViolations(
        array $data,
        array $expectedPropertyPaths,
        array $expectedMessages,
        array $expectedParameters = []
    ): void {
        $this->assertResponseIsUnprocessable();

        $violationKey = $this->getViolationKey();

        $this->assertArrayHasKey($violationKey, $data);

        if (!isset($data[$violationKey])) {
            return;
        }

        [$propertyPaths, $messages] = $this->getViolationValues($data[$violationKey]);

        foreach ($expectedPropertyPaths as $expectedPropertyPath) {
            $this->assertContains($expectedPropertyPath, $propertyPaths);
        }

        foreach ($expectedMessages as $key => $translationKey) {
            $translation = $this->translator->trans($translationKey, $expectedParameters[$key] ?? [], "validators");
            $this->assertContains($translation, $messages);
            // We only want to make sure not full texts are passed
            if (!str_contains($translationKey, " ")) {
                $this->assertNotContains($translationKey, $messages, "Missing translation for $translationKey");
            }
        }

        $this->assertCount(count($expectedMessages), $data[$violationKey]);
    }


    protected function assertTsEquals(
        string $expectedMessage,
        string $message,
        array $params = [],
        string $domain = "validators"
    ) {
        $translation = $this->translator->trans($expectedMessage, $params, $domain);
        $this->assertEquals($translation, $message);
    }


    /**
     * @param array $collection
     * @return array
     */
    private function getViolationValues(array $collection): array
    {
        return match ($this->format) {
            self::FORMAT_JSONLD => [
                $this->getCollectionValue($collection, "propertyPath"),
                $this->getCollectionValue($collection, "message")
            ],
            self::FORMAT_JSONAPI => [
                array_map(function ($value) {
                    $value = explode("/", $value);
                    return $value[count($value) - 1];
                },
                    $this->getCollectionValue($collection, "source.pointer")
                ),
                $this->getCollectionValue($collection, "detail")
            ],
            default => $this->throwUnknownFormatException()
        };
    }


    protected function getCollectionValue(array $collection, string $key): array
    {
        $values = [];
        $key = explode(".", $key);

        foreach ($collection as $item) {
            $value = null;
            foreach ($key as $index => $k) {
                if ($index === 0) {
                    $value = $item[$k];
                } else {
                    $value = $value[$k];
                }
            }

            $values[] = $value;
        }

        return $values;
    }


    /**
     * @throws Exception
     */
    #[ArrayShape(['Content-Type' => "string", "Accept" => "string"])]
    private function getHeaders(array $headers = [], ?string $method = null): array
    {
        if ($this->token && !isset($headers['Authorization'])) {
            $headers['Authorization'] = "Bearer $this->token";
        }

        $contentType = match ($this->format) {
            self::FORMAT_JSONLD => [
                'Content-Type' => $method === HttpOperation::METHOD_PATCH ? "application/merge-patch+json" : "application/ld+json",
                "Accept" => "application/ld+json"
            ],
            self::FORMAT_JSONAPI => [
                'Content-Type' => "application/vnd.api+json",
                "Accept" => "application/vnd.api+json"
            ],
            self::FORMAT_GRAPHQL => [
                'Content-Type' => "application/json",
                "Accept" => "*/*"
            ],
            default => $this->throwUnknownFormatException()
        };

        return array_merge($headers, $contentType);
    }


    private function getViolationKey(): string
    {
        return match ($this->format) {
            self::FORMAT_JSONLD => "violations",
            self::FORMAT_JSONAPI => "errors",
            default => $this->throwUnknownFormatException()
        };
    }

    protected function formatData(array $attributes, array $relationships = []): array
    {
        return match ($this->format) {
            self::FORMAT_JSONLD => array_merge($attributes, $relationships),
            self::FORMAT_JSONAPI => $this->formatDataForJsonApi($attributes, $relationships),
            default => $this->throwUnknownFormatException(),
        };
    }


    #[ArrayShape(["data" => "array[]"])]
    private function formatDataForJsonApi(array $attributes, array $relationships = []): array
    {
        $attributes = [
            "data" => [
                "attributes" => $attributes
            ]
        ];

        if ($relationships) {
            foreach ($relationships as &$relationship) {
                if (is_array($relationship)) {
                    foreach ($relationship as &$item) {
                        $item = [
                            "type" => $item,
                            "id" => $item
                        ];
                    }
                } else {
                    $relationship = [
                        "type" => $relationship,
                        "id" => $relationship
                    ];
                }
            }

            $attributes['data']['relationships'] = $relationships;
        }
        return $attributes;
    }


    private function throwUnknownFormatException()
    {
        throw new Exception("Format $this->format unknown");
    }


    /**
     * @return string
     */
    public function getClass(string $class = null): string
    {
        if ($class) {
            return $class;
        }

        return $this->getDefaultClass();
    }


    /**
     * @return string
     */
    abstract function getDefaultClass(): string;

    /**
     * @return string
     * @throws ResourceClassNotFoundException
     */
    protected function getShortName(string|IEntity $class = null): string
    {
        if ($class instanceof IEntity) {
            $class = $class::class;
        }

        $operation = $this->metadataFactory->create($this->getClass($class))->getOperation();

        return $operation->getShortName();
    }


    protected function getEntity(string $class = null): IEntity
    {
        return $this->getRepository($class)->findOneBy([]);
    }


    protected function getEntityUri(IEntity|string $entity): string|null
    {
        if (is_string($entity)) {
            $entity = $this->getEntity($entity);
        }

        /** @var IriConverterInterface */
        $iriConverter = self::getContainer()->get('api_platform.iri_converter');

        return $iriConverter->getIriFromResource($entity);
    }


    protected function getFormatCollection(array $collection): array
    {
        return match ($this->format) {
            self::FORMAT_JSONLD => $collection['hydra:member'] ?? $collection,
            self::FORMAT_JSONAPI => $this->getJsonApiCollection($collection),
        };
    }

    protected function getJsonAttributes(array $data): array
    {
        return $this->format === self::FORMAT_JSONAPI ? $this->getJsonApiKeys($data['data']) : $data;
    }


    private function getJsonApiCollection(array $collection): array
    {
        $data = [];

        foreach ($collection['data'] as $datum) {
            $data[] = $this->getJsonApiKeys($datum);
        }

        return $data;
    }


    protected function getJsonApiKeys(array $datum): array
    {
        return array_merge([
            "id" => $datum['id'],
            "type" => $datum['type']
        ],
            $datum['attributes'] ?? [],
            $datum['relationships'] ?? [],
        );
    }


    protected function login(User|string $username, string $password = "myAwesomePassword"): array
    {
        if ($username instanceof User) {
            $username = $username->email;
        } else {
            $username = $this->resolveUsername($username);
        }

        $data = $this->post("login", [
            "email" => $username,
            "password" => $password,
        ]);

        // That'll do for now
        if (!isset($data['token'])) {
            throw new Exception($data['message']);
        }

        $this->token = $data['token'];

        return $data;
    }


    /**
     * @param array $collection
     * @param string $key
     * @param array|string $values
     */
    protected function assertCollectionKeyContains(
        array $collection,
        string $key,
        array|string $values,
    ): void {
        if (is_string($values)) {
            $values = [];
        }

        $collection = $this->getFormatCollection($collection);

        $data = $this->getCollectionValue($collection, $key);

        foreach ($values as $test) {
            $this->assertContains($test, $data);
        }
    }


    public function getProfiler(): Profile
    {
        // Check SSE Infos from profiler
        $profiler = self::getContainer()->get('profiler');

        $token = $this->apiClient->getResponse()->getHeaders(false)['x-debug-token'][0];

        /** @var Profile $profile */
        return $profiler->loadProfile($token);
    }

    /**
     * @return Update[]
     */
    public function getMercureMessages(string $hub = "default"): array
    {
        $profile = $this->getProfiler();

        /** @var MercureDataCollector $collector */
        $collector = $profile->getCollector('mercure');

        $hubs = $collector->getHubs();

        return array_map(function (array $message) use ($hub) {
            return $message['object'];
        }, $hubs[$hub]['messages']);
    }


    protected function getMercureData(Update $message): array
    {
        return json_decode($message->getData(), true);
    }

    protected static function populateElasticSearch(string|array $resourceClass = []): void
    {
        $elasticService = self::getContainer()->get(ElasticService::class);

        $elasticService->enabled = true;

        if (is_string($resourceClass)) {
            $resourceClass = [$resourceClass];
        }

        $elasticService->createIndexes($resourceClass);
        $elasticService->loadIndexes($resourceClass);

        sleep(1);
    }


}
