<?php

namespace App\Extension\Elasticsearch;

use ApiPlatform\Elasticsearch\Extension\RequestBodySearchCollectionExtensionInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\IStatusEntity;
use App\Extension\AbstractExtension;

class StatusEntityExtension extends AbstractExtension implements RequestBodySearchCollectionExtensionInterface
{


    public function applyToCollection(
        array $requestBody,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): array {


        if (!is_subclass_of($resourceClass, IStatusEntity::class)) {
            return $requestBody;
        }

        $requestBody['query']["bool"]["must_not"] ??= [];

        $requestBody['query']["bool"]["must_not"][] = [
            "term" => [
                "deleted" => true
            ]
        ];

        return $requestBody;
    }

}
