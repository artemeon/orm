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
    private Converter $converter;
    private QueryBuilder $queryBuilder;

    public function __construct(EntityMeta $entityMeta, ConnectionInterface $connection, Converter $converter)
    {
        $this->entityMeta = $entityMeta;
        $this->connection = $connection;
        $this->converter = $converter;
        $this->queryBuilder = new QueryBuilder($connection, $entityMeta);
    }

    public function map(EntityInterface $entity, array $row): void
    {
        $sourcePrimaryColumn = $this->entityMeta->getPrimaryColumn($entity::class);
        if (!isset($row[$sourcePrimaryColumn])) {
            throw new OrmException('Could not find primary column in result set');
        }

        $properties = $this->entityMeta->getProperties($entity::class);
        foreach ($properties as $config) {
            if ($config[0] === EntityMeta::TYPE_FIELD) {
                [$fieldType, $class, $setter, $getter, $columnName, $dataType, $type, $length, $nullable, $default, $isPrimary] = $config;

                if (!isset($row[$columnName])) {
                    continue;
                }

                $value = $this->converter->toPHPType($row[$columnName], $dataType);
            } elseif ($config[0] === EntityMeta::TYPE_ONE_TO_MANY) {
                [$type, $class, $setter, $getter, $relationTable, $sourceColumn, $targetColumn, $types] = $config;

                $value = new Collection($relationTable, $sourceColumn, $types, $row[$sourcePrimaryColumn], $this->connection, $this, $this->queryBuilder);
            } else {
                throw new OrmException('Provided an invalid property type config');
            }

            $entity->{$setter}($value);
        }
    }

}
