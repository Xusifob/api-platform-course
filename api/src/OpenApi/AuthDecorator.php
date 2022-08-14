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

final class AuthDecorator implements OpenApiFactoryInterface
{
    public function __construct(
        private readonly OpenApiFactoryInterface $decorated
    ) {
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);

        $openApi = $this->buildSchemas($openApi);
        $openApi = $this->addLoginPath($openApi);

        return $this->addRefreshTokenPath($openApi);
    }


    private function addLoginPath(OpenApi $openApi): OpenApi
    {
        $pathItem = new PathItem(
            ref: 'JWT Token',
            post: new Operation(
                operationId: 'postCredentialsItem',
                tags: ['Token'],
                responses: [
                    Response::HTTP_OK => [
                        'description' => 'Get JWT token',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/Token',
                                ],
                            ],
                        ],
                    ],
                ],
                summary: 'Get JWT token to login.',
                requestBody: new RequestBody(
                    description: 'Generate new JWT Token',
                    content: new ArrayObject([
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/Credentials',
                            ],
                        ],
                    ]),
                ),
                security: [],
            ),
        );
        $openApi->getPaths()->addPath('/login', $pathItem);

        return $openApi;
    }

    private function addRefreshTokenPath(OpenApi $openApi): OpenApi
    {
        $pathItem = new PathItem(
            ref: 'JWT Token',
            post: new Operation(
                operationId: 'refreshTokenItem',
                tags: ['Token'],
                responses: [
                    Response::HTTP_OK => [
                        'description' => 'Get JWT token',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/Token',
                                ],
                            ],
                        ],
                    ],
                ],
                summary: 'Refresh expired JWT.',
                requestBody: new RequestBody(
                    description: 'Generate new JWT Token',
                    content: new ArrayObject([
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/RefreshToken',
                            ],
                        ],
                    ]),
                ),
                security: [],
            ),
        );
        $openApi->getPaths()->addPath('/token/refresh', $pathItem);

        return $openApi;
    }


    private function buildSchemas(OpenApi $openApi): OpenApi
    {
        $schemas = $openApi->getComponents()->getSchemas();

        $schemas['Token'] = new ArrayObject([
            'type' => 'object',
            'properties' => [
                'token' => [
                    'type' => 'string',
                    'example' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c',
                    'readOnly' => true,
                ],
                'mercure_token' => [
                    'type' => 'string',
                    'example' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJtZXJjdXJlIjp7InB1Ymxpc2giOltdLCJzdWJzY3JpYmUiOlsiL3VzZXJzLzFlZDFiYjQ5LTljNDgtNjE5OC05MGQ3LTMzYjY2NGMyNzAzNiJdfX0.z3I0aWXMzto1u0EUAuvui6UXWA5ATo-xgCO_xktKKYU',
                    'readOnly' => true,
                ],
                'refresh_token' => [
                    'type' => 'string',
                    'example' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9',
                    'readOnly' => true,
                ],
                'refresh_token_expiration' => [
                    'type' => 'int',
                    'example' => 1_234_569_554_495,
                    'readOnly' => true,
                ]
            ],
        ]);

        $schemas['RefreshToken'] = new ArrayObject([
            'type' => 'object',
            'properties' => [
                'refresh_token' => [
                    'type' => 'string',
                    'required' => true,
                    'example' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9',
                ],
            ],
        ]);

        $schemas['Credentials'] = new ArrayObject([
            'type' => 'object',
            'properties' => [
                'email' => [
                    'type' => 'string',
                    'required' => true,
                    'example' => 'johndoe@example.com',
                ],
                'password' => [
                    'type' => 'string',
                    'required' => true,
                    'example' => 'apassword',
                ],
            ],
        ]);

        $schemas = $openApi->getComponents()->getSecuritySchemes() ?? [];
        $schemas['JWT'] = new ArrayObject([
            'type' => 'http',
            'scheme' => 'bearer',
            'bearerFormat' => 'JWT',
        ]);

        return $openApi;
    }

}
