<?php

declare(strict_types=1);

namespace Artemeon\Orm;

use Artemeon\Database\Schema\DataType;
use Artemeon\Orm\Attribute\OneToMany;
use Artemeon\Orm\Attribute\TableColumn;
use Artemeon\Orm\Attribute\TableName;
use Artemeon\Orm\Attribute\TablePrimary;
use Artemeon\Orm\Exception\OrmException;
use Psr\SimpleCache\CacheInterface;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;

/**
 * The field mapper is a basic service which reads all annotations from a model and maps the values from a row to the model.
 */
class EntityMeta
{
    public const int TYPE_FIELD = 1;

    public const int TYPE_ONE_TO_MANY = 2;

    public function __construct(private readonly CacheInterface $cache)
    {
    }

    /**
     * @param class-string $entityClass
     *
     * @return array<string, mixed>
     */
    public function getProperties(string $entityClass): array
    {
        $cacheKey = 'entity-meta-properties-' . str_replace('\\', '-', $entityClass);
        if ($this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $types = $this->getTypesFromEntity($entityClass);
        $this->cache->set($cacheKey, $types);

        return $types;
    }

    public function getPrimaryColumn(string $entityClass): string
    {
        $types = $this->getProperties($entityClass);
        foreach ($types as $config) {
            if ($config[0] === self::TYPE_FIELD) {
                [$fieldType, $class, $setter, $getter, $columnName, $dataType, $type, $length, $nullable, $default, $isPrimary] = $config;
                if ($class === $entityClass && $isPrimary) {
                    return $columnName;
                }
            }
        }

        throw new OrmException('Could not find primary column for entity ' . $entityClass . ' maybe you have forgotten to add a TablePrimary attribute?');
    }

    public function getPrimaryId(EntityInterface $entity): ?string
    {
        $properties = $this->getProperties($entity::class);
        foreach ($properties as $config) {
            if ($config[0] === EntityMeta::TYPE_FIELD) {
                [$fieldType, $class, $setter, $getter, $columnName, $dataType, $type, $length, $nullable, $default, $isPrimary] = $config;

                if ($isPrimary) {
                    return $entity->{$getter}();
                }
            }
        }

        return null;
    }

    /**
     * @param class-string $entityClass
     *
     * @return array<class-string, string>
     */
    public function getTableNames(string $entityClass): array
    {
        $cacheKey = 'entity-meta-table-names-' . str_replace('\\', '-', $entityClass);
        if ($this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $types = $this->getTableNamesFromEntity($entityClass);
        $this->cache->set($cacheKey, $types);

        return $types;
    }

    /**
     * @param class-string $entityClass
     *
     * @return array<class-string, string>
     */
    private function getTableNamesFromEntity(string $entityClass): array
    {
        $class = new ReflectionClass($entityClass);

        $result = [];
        $tableName = $this->findTableNameAttribute($class);
        if ($tableName instanceof TableName) {
            $result[$class->getName()] = $tableName->tableName;
        }

        while ($parentClass = $class->getParentClass()) {
            $tableName = $this->findTableNameAttribute($parentClass);
            if ($tableName instanceof TableName) {
                $result[$parentClass->getName()] = $tableName->tableName;
            }

            $class = $parentClass;
        }

        return $result;
    }

    /**
     * @param class-string $entityClass
     *
     * @return array<string, array{
     *     self::TYPE_*,
     *     class-string,
     *     non-empty-string,
     *     non-empty-string,
     * }>
     */
    private function getTypesFromEntity(string $entityClass): array
    {
        $class = new ReflectionClass($entityClass);

        if ($class->getParentClass() instanceof ReflectionClass) {
            $result = $this->getTypesFromEntity($class->getParentClass()->getName());
        } else {
            $result = [];
        }

        $properties = $class->getProperties();
        foreach ($properties as $property) {
            $setter = $this->getSetter($class, $property->getName());
            if ($setter === null) {
                continue;
            }

            $getter = $this->getGetter($class, $property->getName());
            if ($getter === null) {
                continue;
            }

            $tableColumn = $this->findTableColumnAttribute($property);
            if ($tableColumn instanceof TableColumn) {
                $columnName = $tableColumn->columnName;
                $dataType = $this->getTypeForProperty($property, $tableColumn->type);

                // BC layer in case the column contains also the table name
                $columnParts = explode('.', $columnName);
                if (count($columnParts) === 2) {
                    $columnName = $columnParts[1];
                }

                $result[$property->getName()] = [self::TYPE_FIELD, $class->getName(), $setter, $getter, $columnName, $dataType, $tableColumn->type, $tableColumn->length, $tableColumn->nullable, $tableColumn->default, $tableColumn instanceof TablePrimary];

                continue;
            }

            $oneToMany = $this->findOneToManyAttribute($property);
            if ($oneToMany instanceof OneToMany) {
                $result[$property->getName()] = [self::TYPE_ONE_TO_MANY, $class->getName(), $setter, $getter, $oneToMany->relationTable, $oneToMany->sourceColumn, $oneToMany->targetColumn, $oneToMany->type];
            }
        }

        return $result;
    }

    private function findTableColumnAttribute(ReflectionProperty $property): ?TableColumn
    {
        foreach ($property->getAttributes() as $attribute) {
            $tableColumn = $attribute->newInstance();
            if ($tableColumn instanceof TableColumn) {
                return $tableColumn;
            }
        }

        return null;
    }

    /**
     * @param ReflectionClass<object> $class
     */
    private function findTableNameAttribute(ReflectionClass $class): ?TableName
    {
        foreach ($class->getAttributes() as $attribute) {
            $tableColumn = $attribute->newInstance();
            if ($tableColumn instanceof TableName) {
                return $tableColumn;
            }
        }

        return null;
    }

    private function findOneToManyAttribute(ReflectionProperty $property): ?OneToMany
    {
        foreach ($property->getAttributes() as $attribute) {
            $tableColumn = $attribute->newInstance();
            if ($tableColumn instanceof OneToMany) {
                return $tableColumn;
            }
        }

        return null;
    }

    private function getTypeForProperty(ReflectionProperty $property, DataType $dataType): DataType | string
    {
        $type = $this->getTypeHintForProperty($property);
        if ($type !== null) {
            return $type;
        }

        return $dataType;
    }

    private function getTypeHintForProperty(ReflectionProperty $property): ?string
    {
        $type = $property->getType();
        if (! $type instanceof ReflectionNamedType) {
            return null;
        }

        return $type->getName();
    }

    /**
     * @param ReflectionClass<object> $class
     */
    private function getSetter(ReflectionClass $class, string $propertyName): ?string
    {
        $setter = null;

        $arrSetters = [
            $propertyName,
            'set' . $propertyName,
            'setStr' . $propertyName,
            'setInt' . $propertyName,
            'setFloat' . $propertyName,
            'setBit' . $propertyName,
            'setObj' . $propertyName,
            'setArr' . $propertyName,
            'setLong' . $propertyName,
            'with' . $propertyName,
        ];

        foreach ($arrSetters as $strOneSetter) {
            if ($class->hasMethod($strOneSetter)) {
                $setter = $strOneSetter;

                break;
            }
        }

        return $setter;
    }

    /**
     * @param ReflectionClass<object> $class
     */
    private function getGetter(ReflectionClass $class, string $propertyName): ?string
    {
        $getter = null;

        $arrGetters = [
            'get' . $propertyName,
            'getStr' . $propertyName,
            'getInt' . $propertyName,
            'getFloat' . $propertyName,
            'getBit' . $propertyName,
            'getObj' . $propertyName,
            'getArr' . $propertyName,
            'getLong' . $propertyName,
            'is' . $propertyName,
            'should' . $propertyName,
        ];

        foreach ($arrGetters as $strOneGetter) {
            if ($class->hasMethod($strOneGetter)) {
                $getter = $strOneGetter;

                break;
            }
        }

        return $getter;
    }
}
