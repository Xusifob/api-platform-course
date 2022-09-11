<?php

namespace App\OpenApi;

use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ArrayObject;
use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\OpenApi;
use ApiPlatform\OpenApi\Model;
use Symfony\Component\HttpFoundation\Response;

final class OpenApiDecorator implements OpenApiFactoryInterface
{
    public function __construct(
        private readonly OpenApiFactoryInterface $decorated
    ) {
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);

        $openApi = $this->buildSchemas($openApi);
        return $this->addDummyPath($openApi);

    }


    private function addDummyPath(OpenApi $openApi): OpenApi
    {
        $pathItem = new PathItem(
            ref: 'Dummy',
            post: new Operation(
                operationId: 'getDummyItem',
                tags: ['Dummy'],
                responses: [
                    Response::HTTP_OK => [
                        'description' => 'Create Dummy Item',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/DummyEntity',
                                ],
                            ],
                        ],
                    ],
                ],
                summary: 'Get the dummy entity data',
                requestBody: new RequestBody(
                    description: 'Get the dummy entity data',
                    content: new ArrayObject([
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/DummyEntity',
                            ],
                        ],
                    ]),
                ),
                security: [],
            ),
        );
        $openApi->getPaths()->addPath('/dummy', $pathItem);

        return $openApi;
    }

    private function buildSchemas(OpenApi $openApi): OpenApi
    {
        $schemas = $openApi->getComponents()->getSchemas();

        $schemas['DummyEntity'] = new ArrayObject([
            'type' => 'object',
            'properties' => [
                'name' => [
                    'type' => 'string',
                    'example' => 'My dummy entity name',
                    'readOnly' => true,
                ],
                'some_boolean' => [
                    'type' => 'boolean',
                    'example' => false,
                ]
            ],
        ]);

        return $openApi;
    }

}
