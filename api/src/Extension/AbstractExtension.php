<?php

namespace App\Extension;

use ReflectionClass;
use ReflectionException;

abstract class AbstractExtension
{

    public static function implements(string $resourceClass, string $interface): bool
    {
        try {
            $class = new ReflectionClass($resourceClass);

            return $class->implementsInterface($interface);
        } catch (ReflectionException) {
            return false;
        }
    }

}
