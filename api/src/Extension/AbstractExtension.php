<?php

namespace App\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ReflectionClass;
use ReflectionException;

abstract class AbstractExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{

    public static function implements(string $resourceClass, string $interface): bool
    {
        try {
            $class = new ReflectionClass($resourceClass);

            return $class->implementsInterface($interface);
        } catch (ReflectionException $exception) {
            return false;
        }
    }

}
