<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;

/**
 * @see https://github.com/Codeception/Codeception/blob/96d4d3ef2bcf5dc0ff158afc96435fd50e80a57e/src/Codeception/Util/ReflectionPropertyAccessor.php
 */
class ReflectionPropertyAccessor
{
    /**
     * @param object $obj
     * @param string $field
     *
     * @return mixed
     *
     * @throws ReflectionException
     */
    public function getProperty($obj, $field)
    {
        if (!$obj || !is_object($obj)) {
            throw new InvalidArgumentException('Cannot get property "' . $field . '" of "' . gettype($obj) . '", expecting object');
        }
        $class = get_class($obj);
        do {
            $reflectedEntity = new ReflectionClass($class);
            if ($reflectedEntity->hasProperty($field)) {
                $property = $reflectedEntity->getProperty($field);
                $property->setAccessible(true);

                return $property->getValue($obj);
            }
            $class = get_parent_class($class);
        } while ($class);

        throw new InvalidArgumentException('Property "' . $field . '" does not exists in class "' . get_class($obj) . '" and its parents');
    }

    public function setConstructorProperties($class, array &$data): object
    {
        $reflectedEntity = new ReflectionClass($class);
        $entity = $reflectedEntity->newInstanceWithoutConstructor();

        $constructorParameters = [];
        $constructor = $reflectedEntity->getConstructor();
        if (null === $constructor) {
            return new $class();
        }
        $constructor->setAccessible(true);
        foreach ($constructor->getParameters() as $parameter) {
            if (array_key_exists($parameter->getName(), $data)) {
                $constructorParameters[] = $data[$parameter->getName()];
                unset($data[$parameter->getName()]);
            } elseif ($parameter->isOptional()) {
                $constructorParameters[] = $parameter->getDefaultValue();
            } else {
                throw new InvalidArgumentException(
                    sprintf('Constructor parameter "%s" missing for class %s', $parameter->getName(), $class)
                );
            }
        }

        $constructor->invoke($entity, ...$constructorParameters);

        return $entity;
    }

    private function setPropertiesForClass($obj, $class, array $data)
    {
        $reflectedEntity = new ReflectionClass($class);
        if (!$obj) {
            $obj = $this->setConstructorProperties($class, $data);
        }
        foreach ($reflectedEntity->getProperties() as $property) {
            if (isset($data[$property->name])) {
                $property->setAccessible(true);
                $property->setValue($obj, $data[$property->name]);
                unset($data[$property->name]);
            }
        }

        return $obj;
    }

    public function setProperties($obj, array $data): void
    {
        if (!$obj || !is_object($obj)) {
            throw new InvalidArgumentException('Cannot set properties for "' . gettype($obj) . '", expecting object');
        }
        $class = get_class($obj);
        do {
            $obj = $this->setPropertiesForClass($obj, $class, $data);
            $class = get_parent_class($class);
        } while ($class);

        // TODO validate that we did not pass unused properties (it has to be done after populateEmbeddables)
//        if (count($data) > 0) {
//            throw new InvalidArgumentException('Invalid properties for class '.get_class($obj).': '.print_r(array_keys($data), true));
//        }
    }

    public function createWithProperties($class, array $data)
    {
        $obj = null;
        do {
            $obj = $this->setPropertiesForClass($obj, $class, $data);
            $class = get_parent_class($class);
        } while ($class);

        // TODO validate that we did not pass unused properties (it has to be done after populateEmbeddables)
//        if (count($data) > 0) {
//            throw new InvalidArgumentException('Invalid properties for class '.get_class($obj).': '.print_r(array_keys($data), true));
//        }

        return $obj;
    }
}
