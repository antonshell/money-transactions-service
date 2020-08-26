<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Faker\Factory;
use InvalidArgumentException;
use ReflectionException;

/**
 * @see https://github.com/Codeception/Codeception/blob/9889794626851cbcb780c4122e56a731611d8c7d/src/Codeception/Module/Doctrine2.php
 */
class AbstractFactory
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var \Faker\Generator
     */
    //protected $faker;

    public function __construct(
        ObjectManager $entityManager
    ) {
        $this->entityManager = $entityManager;

        //$this->faker = Factory::create();
    }

    protected function create($classNameOrInstance, array $data = [])
    {
        // Here we'll have array of all instances (including any relations) created:
        $entities = [];

        if (is_object($classNameOrInstance)) {
            $instance = $this->populateEntity($classNameOrInstance, $data, $entities);
        } elseif (is_string($classNameOrInstance)) {
            $instance = $this->instantiateAndPopulateEntity($classNameOrInstance, $data, $entities);
        } else {
            throw new InvalidArgumentException(sprintf('Doctrine2::haveInRepository expects a class name or instance as first argument, got "%s" instead', gettype($classNameOrInstance)));
        }

        // Flush all changes to database and then refresh all entities. We need this because
        // currently all assignments are done via Reflection API without using setters, which means
        // all OneToMany relations won't get set properly as real setter method would use some
        // Collection operation.
        $this->entityManager->flush();

        foreach ($entities as $entity) {
            $this->entityManager->refresh($entity);
        }

        return $instance;
    }

    private function populateEntity($instance, array $data, array &$instances)
    {
        $rpa = new ReflectionPropertyAccessor();
        $className = get_class($instance);
        $instances[] = $instance;
        [$scalars, $relations] = $this->splitScalarsAndRelations($className, $data);
        $rpa->setProperties(
            $instance,
            array_merge(
                $scalars,
                $this->instantiateRelations($className, $instance, $relations, $instances)
            )
        );
        $this->populateEmbeddables($instance, $data);
        $this->entityManager->persist($instance);

        return $instance;
    }

    private function instantiateRelations($className, $master, array $data, array &$instances): array
    {
        $metadata = $this->entityManager->getClassMetadata($className);
        foreach ($data as $field => $value) {
            if (is_array($value) && $metadata->hasAssociation($field)) {
                unset($data[$field]);
                if ($metadata->isCollectionValuedAssociation($field)) {
                    foreach ($value as $subvalue) {
                        if (!is_array($subvalue)) {
                            throw new InvalidArgumentException('Association "' . $field . '" of entity "' . $className . '" requires array as input, got "' . gettype($subvalue) . '" instead"');
                        }
                        $instance = $this->instantiateAndPopulateEntity(
                            $metadata->getAssociationTargetClass($field),
                            array_merge($subvalue, [
                                $metadata->getAssociationMappedByTargetField($field) => $master,
                            ]),
                            $instances
                        );
                        $instances[] = $instance;
                    }
                } else {
                    $instance = $this->instantiateAndPopulateEntity(
                        $metadata->getAssociationTargetClass($field),
                        $value,
                        $instances
                    );
                    $instances[] = $instance;
                    $data[$field] = $instance;
                }
            }
        }

        return $data;
    }

    private function instantiateAndPopulateEntity($className, array $data, array &$instances)
    {
        $rpa = new ReflectionPropertyAccessor();
        $instance = $rpa->setConstructorProperties($className, $data);
        $this->populateEntity($instance, $data, $instances);

        return $instance;
    }

    private function splitScalarsAndRelations($className, array $data): array
    {
        $scalars = [];
        $relations = [];
        $metadata = $this->entityManager->getClassMetadata($className);
        foreach ($data as $field => $value) {
            if ($metadata->hasAssociation($field)) {
                $relations[$field] = $value;
            } else {
                $scalars[$field] = $value;
            }
        }

        return [$scalars, $relations];
    }

    /**
     * Entity can have embeddable as a field, in which case $data argument of persistEntity() and haveInRepository()
     * could contain keys like {field}.{subField}, where {field} is name of entity's embeddable field, and {subField}
     * is embeddable's field.
     *
     * This method checks if entity has embeddables, and if data have keys as described above, and then uses
     * Reflection API to set values.
     *
     * See https://www.doctrine-project.org/projects/doctrine-orm/en/current/tutorials/embeddables.html for
     * details about this Doctrine feature.
     *
     * @param object $entityObject
     *
     * @throws ReflectionException
     */
    private function populateEmbeddables($entityObject, array $data): void
    {
        $rpa = new ReflectionPropertyAccessor();
        /** @var ClassMetadata $metadata */
        $metadata = $this->entityManager->getClassMetadata(get_class($entityObject));
        foreach (array_keys($metadata->embeddedClasses) as $embeddedField) {
            $embeddedData = [];
            foreach ($data as $entityField => $value) {
                if (!is_string($entityField)) {
                    throw new InvalidArgumentException(sprintf('No field index specified. Please check your data for entity "%s".', get_class($entityObject)));
                }
                $parts = explode('.', $entityField, 2);
                if (2 === count($parts) && $parts[0] === $embeddedField) {
                    $embeddedData[$parts[1]] = $value;
                }
            }
            if ($embeddedData) {
                $rpa->setProperties(
                    $rpa->getProperty($entityObject, $embeddedField),
                    $embeddedData
                );
            }
        }
    }
}
