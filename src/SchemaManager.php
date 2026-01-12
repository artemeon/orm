<?php

declare(strict_types=1);

namespace Artemeon\Orm;

use Artemeon\Database\ConnectionInterface;
use Artemeon\Database\Schema\DataType;
use Artemeon\Orm\Exception\OrmException;

readonly class SchemaManager
{
    public function __construct(
        private ConnectionInterface $connection,
        private EntityMeta $entityMeta,
    ) {
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

    /**
     * @param class-string $entityClass
     * @param list<mixed> $keys
     * @param array<array-key, mixed> $relationTables
     *
     * @return array<string, list<mixed>>
     */
    private function getFieldsForEntity(string $entityClass, array &$keys, array &$relationTables): array
    {
        $properties = $this->entityMeta->getProperties($entityClass);
        $fields = [];

        foreach ($properties as $config) {
            if ($config[0] === EntityMeta::TYPE_FIELD) {
                [, $class,,, $columnName,, $type,, $nullable, $default, $isPrimary] = $config;

                if ($entityClass !== $class) {
                    continue;
                }

                if ($isPrimary) {
                    $keys[] = $columnName;
                }

                $fields[$columnName] = [
                    $type,
                    $nullable,
                    $default,
                ];
            } elseif ($config[0] === EntityMeta::TYPE_ONE_TO_MANY) {
                [,,,, $relationTable, $sourceColumn, $targetColumn] = $config;

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
