<?php

namespace App\Tests\Api;

use ApiPlatform\Api\IriConverterInterface;
use ApiPlatform\Exception\ResourceClassNotFoundException;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\IEntity;
use App\Repository\IRepository;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class ApiTester extends ApiTestCase
{

    use ReloadDatabaseTrait;


    private $apiClient;

    public const FORMAT_JSONLD = "jsonld";

    public const FORMAT_JSONAPI = "jsonapi";

    public const FORMAT_VALUES = [
        self::FORMAT_JSONLD,
        self::FORMAT_JSONAPI
    ];

    protected string $format = self::FORMAT_JSONLD;

    protected TranslatorInterface $translator;

    protected ResourceMetadataCollectionFactoryInterface $metadataFactory;


    protected EntityManagerInterface|null $em;

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


    protected function post(string $url, array $json = []): ?array
    {
        return $this->doRequest("POST", $url, $json);
    }

    protected function put(IEntity|string $url, array $json = []): ?array
    {
        $url = $this->getUrl($url);

        return $this->doRequest("PUT", $url, $json);
    }

    protected function delete(IEntity|string $url): ?array
    {
        $url = $this->getUrl($url);

        return $this->doRequest("DELETE", $url);
    }


    protected function get(IEntity|string $url): ?array
    {
        $url = $this->getUrl($url);

        return $this->doRequest("GET", $url);
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

        throw new \Exception("No url found for entity $url");
    }

    private function doRequest(string $method, string $url, array $json = []): ?array
    {
        try {
            $response = $this->apiClient->request($method, $url, [
                'json' => $json,
                "headers" => $this->getHeaders()
            ]);

            return json_decode($response->getContent(), true);
        } catch (HttpException|ClientException $exception) {
            return json_decode($exception->getResponse()->getContent(false), true);
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
    public function assertHasViolations(array $data, array $expectedPropertyPaths, array $expectedMessages): void
    {
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

        foreach ($expectedMessages as $expectedMessage) {
            $translation = $this->translator->trans($expectedMessage, [], "validators");
            $this->assertContains($translation, $messages);
            $this->assertNotContains($expectedMessage, $messages);
        }

        $this->assertCount(count($expectedMessages), $data[$violationKey]);
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
     * @throws \Exception
     */
    #[ArrayShape(['Content-Type' => "string", "Accept" => "string"])]
    private function getHeaders(): array
    {
        return match ($this->format) {
            self::FORMAT_JSONLD => [
                'Content-Type' => "application/ld+json",
                "Accept" => "application/ld+json"
            ],
            self::FORMAT_JSONAPI => [
                'Content-Type' => "application/vnd.api+json",
                "Accept" => "application/vnd.api+json"
            ],
            default => $this->throwUnknownFormatException()
        };
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
        throw new \Exception("Format $this->format unknown");
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


    protected function getRepository(string $class = null): IRepository
    {
        $this->em->clear();

        return $this->em->getRepository($this->getClass($class));
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


}
