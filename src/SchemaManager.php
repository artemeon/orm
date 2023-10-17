<?php

namespace Artemeon\Orm;

use Artemeon\Database\ConnectionInterface;
use Artemeon\Database\Schema\DataType;
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
        $relationTables = [];

        foreach ($tableNames as $class => $tableName) {
            $primaryKeys = [];
            $fields = $this->getFieldsForEntity($class, $primaryKeys, $relationTables);
            $this->connection->createTable($tableName, $fields, $primaryKeys);
        }

        foreach ($relationTables as $tableName => $config) {
            [$fields, $primaryKeys] = $config;
            $this->connection->createTable($tableName, $fields, $primaryKeys);
        }
    }

    private function getFieldsForEntity(string $entityClass, array &$keys, array &$relationTables): array
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
                [$type, $class, $setter, $getter, $relationTable, $sourceColumn, $targetColumn, $types] = $config;

                $relationColumns = [
                    $sourceColumn => [DataType::CHAR20, false],
                    $targetColumn => [DataType::CHAR20, false],
                ];

                $primaryKeys = [$sourceColumn, $targetColumn];

                $relationTables[$relationTable] = [$relationColumns, $primaryKeys];
            } else {
                throw new OrmException('Provided an invalid property type config');
            }
        }

        return $fields;
    }
}
