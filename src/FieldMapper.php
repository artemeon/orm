<?php

namespace Artemeon\Orm;

use Artemeon\Database\ConnectionInterface;
use Artemeon\Database\Schema\DataType;
use Artemeon\Orm\Exception\OrmException;

/**
 * The field mapper is a basic service which reads all annotations from a model and maps the values from a row to the model
 */
class FieldMapper
{
    private EntityMeta $entityMeta;
    private ConnectionInterface $connection;
    private QueryBuilder $queryBuilder;

    public function __construct(EntityMeta $entityMeta, ConnectionInterface $connection)
    {
        $this->entityMeta = $entityMeta;
        $this->connection = $connection;
        $this->queryBuilder = new QueryBuilder($connection, $entityMeta);
    }

    public function map(EntityInterface $entity, array $row): void
    {
        $properties = $this->entityMeta->getProperties(get_class($entity));
        foreach ($properties as $config) {
            if ($config[0] === EntityMeta::TYPE_FIELD) {
                [$type, $class, $setter, $column, $type, $isPrimary] = $config;

                if (!isset($row[$column])) {
                    continue;
                }

                $value = $this->convertToDataType($row[$column], $type);
            } elseif ($config[0] === EntityMeta::TYPE_ONE_TO_MANY) {
                $sourcePrimaryColumn = $this->entityMeta->getPrimaryColumn(get_class($entity));
                if (!isset($row[$sourcePrimaryColumn])) {
                    throw new OrmException('Could not find primary column in result set');
                }

                [$type, $class, $setter, $relationTable, $sourceColumn, $targetColumn, $targetClass] = $config;

                $value = new Collection($relationTable, $sourceColumn, $targetColumn, $targetClass, $row[$sourcePrimaryColumn], $this->connection, $this, $this->queryBuilder);
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
}
