<?php

namespace App\Serializer;

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Serializer\SerializerContextBuilderInterface;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Security\Core\Security;

use function Symfony\Component\String\u;

class SerialisationGroupGenerator implements SerializerContextBuilderInterface
{


    public function __construct(
        private readonly Security $security,
        private readonly ResourceMetadataCollectionFactoryInterface $metadataFactory,
        private readonly SerializerContextBuilderInterface $decorated
    ) {
    }


    public function createFromRequest(Request $request, bool $normalization, array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        $groups = $this->buildGroupsFromRequest($request, $normalization);

        $context['groups'] = [...$context['groups'] ?? [], ...$groups];

        return $context;
    }


    private function buildGroupsFromRequest(Request $request, bool $normalization): array
    {
        $shortName = $this->extractShortName($request);
        $operationType = $this->extractOperationType($request);
        $method = $this->extractMethod($request);
        $role = $this->getRole();
        $process = $normalization ? "read" : "write";

        return [
            "$process",
            "$shortName",
            "$shortName:$process",
            "$shortName:$method",
            "$shortName:$operationType",
            "$role:$process",
            "$role:$shortName",
            "$role:$shortName:$process",
            "$role:$shortName:$method",
            "$role:$shortName:$operationType",
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

        $isCollection = preg_match("#get_collection$#", (string)$route);

        return $isCollection ? "collection" : "item";
    }


    private function extractMethod(Request $request): string
    {
        return strtolower($request->getMethod());
    }

    private function getRole(): string
    {
        $user = $this->security->getUser();

        if (!($user instanceof User)) {
            return "role_public";
        }

        return strtolower($user->getRole()->value);
    }

}
