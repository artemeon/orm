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
    private readonly QueryBuilder $queryBuilder;

    public function __construct(private readonly EntityMeta $entityMeta, private readonly ConnectionInterface $connection, private readonly Converter $converter)
    {
        $this->queryBuilder = new QueryBuilder($this->connection, $this->entityMeta);
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
