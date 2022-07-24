<?php

namespace App\Tests\Api;

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class ApiTester extends ApiTestCase
{

    public const FORMAT_JSONLD = "jsonld";

    public const FORMAT_JSONAPI = "jsonapi";

    public const FORMAT_VALUES = [
        //   self::FORMAT_JSONLD,
        self::FORMAT_JSONAPI
    ];

    protected string $format = self::FORMAT_JSONLD;

    protected TranslatorInterface $translator;

    protected ResourceMetadataCollectionFactoryInterface $metadataFactory;


    public function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        $this->translator = self::getContainer()->get(TranslatorInterface::class);
        $this->metadataFactory = self::getContainer()->get(ResourceMetadataCollectionFactoryInterface::class);
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



    protected function post(string $url, array $json = []): array
    {
        try {
            $response = self::createClient()->request("POST", $url, [
                'json' => $this->formatData($json),
                "headers" => $this->getHeaders()
            ]);

            return json_decode($response->getContent(), true);
        } catch (ClientException $exception) {
            return json_decode($exception->getResponse()->getContent(false), true);
        }
    }


    /**
     * @param array $data
     * @param array $postData
     * @return void
     * @throws \ApiPlatform\Exception\ResourceClassNotFoundException
     */
    public function assertResponseHasPostData(array $data, array $postData)
    {
        match ($this->format) {
            self::FORMAT_JSONLD => $this->assertJsonLdResponsePostData($data, $postData),
            self::FORMAT_JSONAPI => $this->assertJsonApiResponsePostData($data, $postData),
        };
    }


    private function assertJsonLdResponsePostData(array $data, array $postData): void
    {
        $context = $this->getContext();

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

        $context = $this->getContext();

        $this->assertEquals($context, $data["type"]);
        $this->assertArrayHasKey("id", $data);

        foreach ($postData as $key => $postDatum) {
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

    private function formatData(array $data): array
    {
        return match ($this->format) {
            self::FORMAT_JSONLD => $data,
            self::FORMAT_JSONAPI => [
                "data" => [
                    "attributes" => $data
                ]
            ],
            default => $this->throwUnknownFormatException()
        };
    }


    private function throwUnknownFormatException()
    {
        throw new \Exception("Format $this->format unknown");
    }


    /**
     * @return string
     */
    abstract function getClass(): string;

    /**
     * @return string
     * @throws \ApiPlatform\Exception\ResourceClassNotFoundException
     */
    private function getContext(): string
    {
        $operation = $this->metadataFactory->create($this->getClass())->getOperation();

        return $operation->getShortName();
    }


}
