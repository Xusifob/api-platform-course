<?php

namespace App\Serializer;

use ApiPlatform\Metadata\Resource\Factory\AttributesResourceMetadataCollectionFactory;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceMetadataCollection;
use ApiPlatform\Serializer\SerializerContextBuilderInterface;
use Symfony\Component\HttpFoundation\Request;

use function Symfony\Component\String\u;

class SerialisationGroupGenerator implements SerializerContextBuilderInterface
{


    private SerializerContextBuilderInterface $decorated;

    protected ResourceMetadataCollectionFactoryInterface $metadataFactory;


    public function __construct(
        ResourceMetadataCollectionFactoryInterface $metadataFactory,
        SerializerContextBuilderInterface $decorated
    ) {
        $this->metadataFactory = $metadataFactory;
        $this->decorated = $decorated;
    }


    public function createFromRequest(Request $request, bool $normalization, array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        $groups = $this->buildGroupsFromRequest($request);

        $context['groups'] = [...$context['groups'] ?? [], ...$groups];

        return $context;
    }


    private function buildGroupsFromRequest(Request $request): array
    {
        $shortName = $this->extractShortName($request);
        $operationType = $this->extractOperationType($request);
        $method = $this->extractMethod($request);


        return [
            $shortName,
            "$shortName:$method",
            "$shortName:$operationType"
        ];
    }


    private function extractShortName(Request $request): string
    {
        $class = $request->attributes->get("_api_resource_class", null);

        $metadata = $this->metadataFactory->create($class);

        $shortName = $metadata->getOperation()->getShortName();

        // https://symfony.com/doc/current/components/string.html#methods-to-change-case
        return u($shortName)->snake()->toString();
    }


    private function extractOperationType(Request $request): string
    {
        $route = $request->attributes->get("_route");

        $isCollection = preg_match("#get_collection$#", $route);

        return $isCollection ? "collection" : "item";
    }


    private function extractMethod(Request $request): string
    {
        return strtolower($request->getMethod());
    }


}
