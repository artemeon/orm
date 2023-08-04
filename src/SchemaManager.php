<?php

namespace Artemeon\Orm;

use Artemeon\Database\ConnectionInterface;
use Artemeon\Orm\Exception\OrmException;

class SchemaManager
{
    private ConnectionInterface $connection;
    private EntityMeta $entityMeta;

    public function __construct(ConnectionInterface $connection, EntityMeta $entityMeta)
    {
        $this->connection = $connection;
        $this->entityMeta = $entityMeta;
    }

    public function createTable(string $entityClass): void
    {
        $tableNames = $this->entityMeta->getTableNames($entityClass);

        foreach ($tableNames as $class => $tableName) {
            $keys = [];
            $fields = $this->getFieldsForEntity($class, $keys);
            $this->connection->createTable($tableName, $fields, $keys);
        }
    }

    private function getFieldsForEntity(string $entityClass, array &$keys): array
    {
        $properties = $this->entityMeta->getProperties($entityClass);
        $fields = [];

        foreach ($properties as $config) {
            if ($config[0] === EntityMeta::TYPE_FIELD) {
                [$fieldType, $class, $setter, $getter, $columnName, $dataType, $type, $length, $nullable, $default, $isPrimary] = $config;

                if ($entityClass !== $class) {
                    continue;
                }

                if ($isPrimary) {
                    $keys[] = $columnName;
                }

                $fields[$columnName] = [
                    $type,
                    $nullable,
                    $default
                ];
            } elseif ($config[0] === EntityMeta::TYPE_ONE_TO_MANY) {
                [$type, $class, $setter, $relationTable, $sourceColumn, $targetColumn, $targetClass] = $config;

            } else {
                throw new OrmException('Provided an invalid property type config');
            }
        }

        return $fields;
    }
}
