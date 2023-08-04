<?php

namespace Artemeon\Orm;

use Artemeon\Database\ConnectionInterface;
use Artemeon\Orm\Exception\OrmException;

class EntityManager
{
    private ConnectionInterface $connection;
    private QueryBuilder $queryBuilder;
    private FieldMapper $fieldMapper;
    private EntityMeta $entityMeta;
    private Converter $converter;

    public function __construct(ConnectionInterface $connection, QueryBuilder $queryBuilder, FieldMapper $fieldMapper, EntityMeta $entityMeta, Converter $converter)
    {
        $this->connection = $connection;
        $this->queryBuilder = $queryBuilder;
        $this->fieldMapper = $fieldMapper;
        $this->entityMeta = $entityMeta;
        $this->converter = $converter;
    }

    /**
     * @param array<ConditionInterface> $conditions
     * @param array<OrderByInterface> $sorting
     * @return array<EntityInterface>
     * @throws Exception\OrmException
     */
    public function findAll(string $targetClass, array $conditions = [], array $sorting = []): array
    {
        [$query, $params] = $this->getQuery($targetClass, $conditions, $sorting);

        $entities = [];
        $result = $this->connection->fetchAllAssociative($query, $params);
        foreach ($result as $row) {
            $entity = new $targetClass();
            $this->fieldMapper->map($entity, $row);
            $entities[] = $entity;
        }

        return $entities;
    }

    /**
     * @param array<ConditionInterface> $conditions
     * @param array<OrderByInterface> $sorting
     * @throws Exception\OrmException
     */
    public function findOne(string $targetClass, array $conditions = [], array $sorting = []): ?EntityInterface
    {
        [$query, $params] = $this->getQuery($targetClass, $conditions, $sorting);

        $row = $this->connection->fetchAssociative($query, $params);
        if (empty($row)) {
            return null;
        }

        $entity = new $targetClass();
        $this->fieldMapper->map($entity, $row);
        return $entity;
    }

    /**
     * @param array<ConditionInterface> $conditions
     * @throws Exception\OrmException
     */
    public function getCount(string $targetClass, array $conditions): int
    {
        $from = $this->queryBuilder->buildFrom($targetClass);
        $query = 'SELECT COUNT(*) AS cnt ' . $from . ' WHERE 1=1 ';

        $params = [];
        foreach ($conditions as $condition) {
            $query.= $condition->getWhere() . ' ';
            $params = array_merge($params, $condition->getParams());
        }

        $row = $this->connection->fetchOne($query, $params);
        if (!isset($row['cnt'])) {
            return 0;
        }

        return (int) $row['cnt'];
    }

    private function getQuery(string $targetClass, array $conditions = [], array $sorting = []): array
    {
        $from = $this->queryBuilder->buildFrom($targetClass);
        $query = 'SELECT * ' . $from . ' WHERE 1=1 ';

        $params = [];
        foreach ($conditions as $condition) {
            if (!$condition instanceof Condition) {
                continue;
            }

            $query.= $condition->getWhere() . ' ';
            $params = array_merge($params, $condition->getParams());
        }

        if (count($sorting) > 0) {
            $query.= ' ORDER BY ';
            foreach ($sorting as $sort) {
                if (!$sort instanceof OrderBy) {
                    continue;
                }

                $query.= $sort->getOrderBy() . ' ';
            }
        }

        return [$query, $params];
    }

    public function insert(EntityInterface $entity): void
    {
        $properties = $this->entityMeta->getProperties($entity::class);
        $tableNames = $this->entityMeta->getTableNames($entity::class);

        $this->connection->transactionBegin();

        $data = [];
        $relations = [];
        foreach ($properties as $config) {
            if ($config[0] === EntityMeta::TYPE_FIELD) {
                [$fieldType, $class, $setter, $getter, $columnName, $dataType, $type, $length, $nullable, $default, $isPrimary] = $config;

                $tableName = $tableNames[$class];
                if (!isset($data[$tableName])) {
                    $data[$tableName] = [];
                }

                $data[$tableName][$columnName] = $this->converter->toDatabaseType($entity->{$getter}(), $type);
            } elseif ($config[0] === EntityMeta::TYPE_ONE_TO_MANY) {
                $sourcePrimaryColumn = $this->entityMeta->getPrimaryColumn($entity::class);
                if (!isset($row[$sourcePrimaryColumn])) {
                    throw new OrmException('Could not find primary column in result set');
                }

                [$type, $class, $setter, $getter, $relationTable, $sourceColumn, $targetColumn, $targetClass] = $config;

                $value = $entity->{$getter}();
                if ($value === null) {
                    continue;
                }

                if (!$value instanceof Collection) {
                    throw new OrmException('Provided one to many property must return a ' . Collection::class);
                }

                $relations[] = [$relationTable, $sourceColumn, $targetColumn, $targetClass];
            } else {
                throw new OrmException('Provided an invalid property type config');
            }
        }

        foreach ($data as $tableName => $values) {
            $this->connection->insert($tableName, $values);
        }

        foreach ($relations as $relation) {
            [$relationTable, $sourceColumn, $targetColumn, $targetClass] = $relation;

            $this->connection->delete($relationTable, [$sourceColumn => $row[$sourcePrimaryColumn]]);
            foreach ($value as $relationEntity) {
                $this->connection->insert($relationTable, [
                    $sourceColumn => $row[$sourcePrimaryColumn],
                    $targetColumn => $relationEntity,
                ]);
            }
        }

        $this->connection->transactionCommit();
    }

    public function update(EntityInterface $entity): void
    {

    }

    public function delete(EntityInterface $entity): void
    {

    }
}
