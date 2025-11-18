<?php

namespace BandwidthLib\Messaging\Models\Traits;

use Exception;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;

trait FromArray
{
    /**
     * @throws Exception
     * @param mixed[] $data
     * @return static
     */
    public static function fromArray(array $data): static
    {
        $reflection = new ReflectionClass(static::class);
        $instance = $reflection->newInstanceWithoutConstructor();

        foreach ($data as $key => $value) {
            if ($reflection->hasProperty($key)) {
                $property = $reflection->getProperty($key);

                if (!$property->isPublic()) {
                    $property->setAccessible(true);
                }

                $value = static::fromArrayValue($property, $value);

                $property->setValue($instance, $value);
            }
        }

        return $instance;
    }

    /**
     * @param mixed[] $data
     * @return null|static
     */
    public static function tryFromArray(array $data): ?static
    {
        try {
            return static::fromArray($data);
        } catch (Exception $e) {
            return null;
        }
    }

    protected static function fromArrayValue(
        ReflectionProperty $property,
        mixed $value,
    ): mixed {
        $type = $property->getType();

        if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
            $className = $type->getName();

            if (method_exists($className, "fromArray")) {
                return $className::fromArray($value);
            }
        }

        if (!is_array($value)) {
            return $value;
        }

        // Handle arrays of objects using PHPDoc type hints like:
        // 1. @var array<ClassName>
        // 2. @var ClassName[]
        $docComment = $property->getDocComment();
        if (
            $docComment &&
            preg_match(
                "/@var\s+array<(\w+)>|@var\s+(\w+)\[\]/",
                $docComment,
                $matches,
            )
        ) {
            $className = $matches[1] ?? $matches[2];

            $propertyClass = $property->getDeclaringClass();
            $namespace = $propertyClass->getNamespaceName();

            $possibleClasses = [
                $namespace ? "{$namespace}\\{$className}" : $className,
                $className,
            ];

            foreach ($possibleClasses as $fullClassName) {
                if (
                    class_exists($fullClassName) &&
                    method_exists($fullClassName, "fromArray")
                ) {
                    return array_map(
                        fn($item) => is_array($item)
                            ? $fullClassName::fromArray($item)
                            : $item,
                        $value,
                    );
                }
            }
        }

        // As-is by default.
        return $value;
    }
}
