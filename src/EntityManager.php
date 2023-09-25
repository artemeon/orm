<?php

namespace Artemeon\Orm;

use Artemeon\Database\ConnectionInterface;
use Artemeon\Orm\Exception\OrmException;
use Doctrine\Common\Collections\Collection as DoctrineCollection;

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

    public function insert(EntityInterface $entity): string
    {
        $properties = $this->entityMeta->getProperties($entity::class);
        $tableNames = $this->entityMeta->getTableNames($entity::class);

        $systemId = $this->generateSystemId();

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

                if ($isPrimary) {
                    $data[$tableName][$columnName] = $systemId;
                    $entity->{$setter}($systemId);
                } else {
                    $data[$tableName][$columnName] = $this->converter->toDatabaseType($entity->{$getter}(), $type);
                }
            } elseif ($config[0] === EntityMeta::TYPE_ONE_TO_MANY) {
                [$type, $class, $setter, $getter, $relationTable, $sourceColumn, $targetColumn, $targetClass] = $config;

                $value = $entity->{$getter}();
                if ($value === null) {
                    continue;
                }

                if (!$value instanceof DoctrineCollection) {
                    throw new OrmException('Provided one to many property must return a ' . DoctrineCollection::class);
                }

                $relations[] = [$value, $relationTable, $sourceColumn, $targetColumn, $targetClass];
            } else {
                throw new OrmException('Provided an invalid property type config');
            }
        }

        foreach ($data as $tableName => $values) {
            $this->connection->insert($tableName, $values);
        }

        foreach ($relations as $relation) {
            [$collection, $relationTable, $sourceColumn, $targetColumn, $targetClass] = $relation;

            $this->connection->delete($relationTable, [$sourceColumn => $sourcePrimaryId]);
            foreach ($collection as $relationEntity) {
                $relationEntityId = $this->getPrimaryId($relationEntity);
                if (empty($relationEntityId)) {
                    $relationEntityId = $this->insert($relationEntity);
                }

                $this->connection->insert($relationTable, [
                    $sourceColumn => $sourcePrimaryId,
                    $targetColumn => $relationEntityId,
                ]);
            }
        }

        $this->connection->transactionCommit();

        return $systemId;
    }

    public function update(EntityInterface $entity): void
    {
        $properties = $this->entityMeta->getProperties($entity::class);
        $tableNames = $this->entityMeta->getTableNames($entity::class);

        $this->connection->transactionBegin();

        $data = [];
        $relations = [];
        $identifiers = [];
        foreach ($properties as $config) {
            if ($config[0] === EntityMeta::TYPE_FIELD) {
                [$fieldType, $class, $setter, $getter, $columnName, $dataType, $type, $length, $nullable, $default, $isPrimary] = $config;

                $tableName = $tableNames[$class];
                if (!isset($data[$tableName])) {
                    $data[$tableName] = [];
                }
                if (!isset($identifiers[$tableName])) {
                    $identifiers[$tableName] = [];
                }

                if ($isPrimary) {
                    $identifiers[$tableName][$columnName] = $entity->{$getter}();
                } else {
                    $data[$tableName][$columnName] = $this->converter->toDatabaseType($entity->{$getter}(), $type);
                }
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

                $relations[] = [$value, $row[$sourcePrimaryColumn], $relationTable, $sourceColumn, $targetColumn, $targetClass];
            } else {
                throw new OrmException('Provided an invalid property type config');
            }
        }

        foreach ($data as $tableName => $values) {
            $this->connection->update($tableName, $values, $identifiers[$tableName] ?? throw new OrmException('No primary key exists for table ' . $tableName));
        }

        foreach ($relations as $relation) {
            [$collection, $sourcePrimaryId, $relationTable, $sourceColumn, $targetColumn, $targetClass] = $relation;

            $this->connection->delete($relationTable, [$sourceColumn => $sourcePrimaryId]);
            foreach ($collection as $relationEntity) {
                $relationEntityId = $this->getPrimaryId($relationEntity);
                if (empty($relationEntityId)) {
                    $relationEntityId = $this->insert($relationEntity);
                }

                $this->connection->insert($relationTable, [
                    $sourceColumn => $sourcePrimaryId,
                    $targetColumn => $relationEntityId,
                ]);
            }
        }

        $this->connection->transactionCommit();
    }

    public function delete(EntityInterface $entity): void
    {
        $properties = $this->entityMeta->getProperties($entity::class);
        $tableNames = $this->entityMeta->getTableNames($entity::class);

        $this->connection->transactionBegin();

        $relations = [];
        $identifiers = [];
        foreach ($properties as $config) {
            if ($config[0] === EntityMeta::TYPE_FIELD) {
                [$fieldType, $class, $setter, $getter, $columnName, $dataType, $type, $length, $nullable, $default, $isPrimary] = $config;

                $tableName = $tableNames[$class];
                if (!isset($identifiers[$tableName])) {
                    $identifiers[$tableName] = [];
                }

                if ($isPrimary) {
                    $identifiers[$tableName][$columnName] = $entity->{$getter}();
                }
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

                $relations[] = [$value, $row[$sourcePrimaryColumn], $relationTable, $sourceColumn, $targetColumn, $targetClass];
            } else {
                throw new OrmException('Provided an invalid property type config');
            }
        }

        foreach ($identifiers as $tableName => $identifier) {
            $this->connection->delete($tableName, $identifier);
        }

        foreach ($relations as $relation) {
            [$collection, $sourcePrimaryId, $relationTable, $sourceColumn, $targetColumn, $targetClass] = $relation;

            $this->connection->delete($relationTable, [$sourceColumn => $sourcePrimaryId]);
        }

        $this->connection->transactionCommit();
    }

    private function getPrimaryId(EntityInterface $entity): ?string
    {
        $properties = $this->entityMeta->getProperties($entity::class);
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

    private function generateSystemId(): string
    {
        return substr(sha1(uniqid()), 0, 20);
    }
}
