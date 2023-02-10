<?php

namespace Artemeon\Orm;

use Artemeon\Database\Schema\DataType;
use Artemeon\Orm\Attribute\OneToMany;
use Artemeon\Orm\Attribute\TableColumn;
use Artemeon\Orm\Exception\OrmException;
use Psr\SimpleCache\CacheInterface;

/**
 * The field mapper is a basic service which reads all annotations from a model and maps the values from a row to the model
 */
class FieldMapper
{
    private CacheInterface $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function map(EntityInterface $entity, array $row): void
    {
        $cacheKey = 'entity-property-types-' . str_replace('\\', '-', get_class($entity));
        if ($this->cache->has($cacheKey)) {
            $types = $this->cache->get($cacheKey);
        } else {
            $types = $this->getPropertyTypes($entity);
            $this->cache->set($cacheKey, $types);
        }

        foreach ($types as $propertyName => $config) {
            $length = count($config);
            if ($length === 3) {
                [$setter, $column, $type] = $config;

                $columnParts = explode('.', $column);
                if (count($columnParts) === 2) {
                    $column = $columnParts[1];
                }

                if (!isset($row[$column])) {
                    continue;
                }

                $value = $this->convertToDataType($row[$column], $type);
            } elseif ($length === 4) {
                [$setter, $targetTable, $sourceColumn, $targetColumn] = $config;

                $value = new OrmAssignmentArray($entity, $propertyName);
            } else {
                throw new OrmException('Provided an invalid property type config');
            }

            $entity->{$setter}($value);
        }
    }

    /**
     * Casts the values datatype based on the value of the var annotation.
     */
    public function convertToDataType(mixed $value, string $dataType): mixed
    {
        return match ($dataType) {
            'int', 'long', DataType::STR_TYPE_INT, DataType::STR_TYPE_BIGINT, DataType::STR_TYPE_LONG => (int) $value,
            'float', DataType::STR_TYPE_FLOAT, DataType::STR_TYPE_DOUBLE => (float) $value,
            'string', DataType::STR_TYPE_CHAR10, DataType::STR_TYPE_CHAR20, DataType::STR_TYPE_CHAR100, DataType::STR_TYPE_CHAR254, DataType::STR_TYPE_CHAR500, DataType::STR_TYPE_TEXT, DataType::STR_TYPE_LONGTEXT => $value,
            'bool', 'boolean' => (bool) $value,
            default => new $dataType($value),
        };
    }

    private function getPropertyTypes(EntityInterface $entity): array
    {
        $class = new \ReflectionClass(get_class($entity));
        $result = [];
        $properties = $class->getProperties();
        foreach ($properties as $property) {
            $setter = $this->getSetter($entity, $property->getName());
            if ($setter === null) {
                continue;
            }

            $tableColumn = $this->findTableColumnAttribute($property);
            if ($tableColumn instanceof TableColumn) {
                $columnName = $tableColumn->columnName;
                $type = $this->getTypeForProperty($property, $tableColumn->dataType);

                $result[$property->getName()] = [$setter, $columnName, $type];
                continue;
            }

            $oneToMany = $this->findOneToManyAttribute($property);
            if ($oneToMany instanceof OneToMany) {
                $result[$property->getName()] = [$setter, $oneToMany->targetTable, $oneToMany->sourceColumn, $oneToMany->targetColumn];
            }
        }

        return $result;
    }

    private function findTableColumnAttribute(\ReflectionProperty $property): ?TableColumn
    {
        foreach ($property->getAttributes() as $attribute) {
            $tableColumn = $attribute->newInstance();
            if ($tableColumn instanceof TableColumn) {
                return $tableColumn;
            }
        }

        return null;
    }

    private function findOneToManyAttribute(\ReflectionProperty $property): ?OneToMany
    {
        foreach ($property->getAttributes() as $attribute) {
            $tableColumn = $attribute->newInstance();
            if ($tableColumn instanceof OneToMany) {
                return $tableColumn;
            }
        }

        return null;
    }

    private function getTypeForProperty(\ReflectionProperty $property, string $dataType): string
    {
        $type = $this->getTypeHintForProperty($property);
        if ($type !== null) {
            return $type;
        }

        return $dataType;
    }

    private function getTypeHintForProperty(\ReflectionProperty $property): ?string
    {
        $type = $property->getType();
        if (!$type instanceof \ReflectionNamedType) {
            return null;
        }

        return $type->getName();
    }

    private function getSetter(EntityInterface $entity, string $propertyName): ?string
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
            if (method_exists($entity, $strOneSetter)) {
                $setter = $strOneSetter;
                break;
            }
        }

        return $setter;
    }
}
