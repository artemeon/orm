<?php

declare(strict_types=1);

namespace Artemeon\Orm;

use Artemeon\Database\ConnectionInterface;
use Artemeon\Orm\Exception\OrmException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection as DoctrineCollection;

class EntityManager
{
    public function __construct(private readonly ConnectionInterface $connection, private readonly QueryBuilder $queryBuilder, private readonly FieldMapper $fieldMapper, private readonly EntityMeta $entityMeta, private readonly Converter $converter)
    {
    }

    /**
     * @param class-string<EntityInterface> $targetClass
     * @param list<ConditionInterface> $conditions
     * @param list<OrderByInterface> $sorting
     *
     * @throws OrmException
     *
     * @return array<EntityInterface>
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
     * @param class-string<EntityInterface> $targetClass
     * @param list<ConditionInterface> $conditions
     * @param list<OrderByInterface> $sorting
     *
     * @throws OrmException
     */
    public function findOne(string $targetClass, array $conditions = [], array $sorting = []): ?EntityInterface
    {
        [$query, $params] = $this->getQuery($targetClass, $conditions, $sorting);

        $row = $this->connection->fetchAssociative($query, $params);
        if ($row === [] || $row === false) {
            return null;
        }

        $entity = new $targetClass();
        $this->fieldMapper->map($entity, $row);

        return $entity;
    }

    /**
     * @param class-string $targetClass
     * @param array<ConditionInterface> $conditions
     *
     * @throws OrmException
     */
    public function getCount(string $targetClass, array $conditions): int
    {
        $from = $this->queryBuilder->buildFrom($targetClass);
        $query = 'SELECT COUNT(*) AS cnt ' . $from . ' WHERE 1=1 ';

        $params = [];
        foreach ($conditions as $condition) {
            $query .= $condition->getWhere() . ' ';
            $params = array_merge($params, $condition->getParams());
        }

        $row = $this->connection->fetchOne($query, $params);
        if (! isset($row['cnt'])) {
            return 0;
        }

        return (int) $row['cnt'];
    }

    /**
     * @param class-string $targetClass
     * @param list<ConditionInterface> $conditions
     * @param list<OrderByInterface> $sorting
     *
     * @return array{string, list<mixed>}
     */
    private function getQuery(string $targetClass, array $conditions = [], array $sorting = []): array
    {
        $from = $this->queryBuilder->buildFrom($targetClass);
        $query = 'SELECT * ' . $from . ' WHERE 1=1 ';

        $params = [];
        foreach ($conditions as $condition) {
            if (! $condition instanceof Condition) {
                continue;
            }

            $query .= $condition->getWhere() . ' ';
            $params = array_merge($params, $condition->getParams());
        }

        if ($sorting !== []) {
            $query .= ' ORDER BY ';
            foreach ($sorting as $sort) {
                if (! $sort instanceof OrderBy) {
                    continue;
                }

                $query .= $sort->getOrderBy() . ' ';
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
                if (! isset($data[$tableName])) {
                    $data[$tableName] = [];
                }

                if ($isPrimary) {
                    $data[$tableName][$columnName] = $systemId;
                    $entity->{$setter}($systemId);
                } else {
                    $data[$tableName][$columnName] = $this->converter->toDatabaseType($entity->{$getter}(), $type);
                }
            } elseif ($config[0] === EntityMeta::TYPE_ONE_TO_MANY) {
                $relations[] = $this->getRelation($entity, $config);
            } else {
                throw new OrmException('Provided an invalid property type config');
            }
        }

        foreach ($data as $tableName => $values) {
            $this->connection->insert($tableName, $values);
        }

        $this->handleRelations($entity, $relations);

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
                if (! isset($data[$tableName])) {
                    $data[$tableName] = [];
                }

                if (! isset($identifiers[$tableName])) {
                    $identifiers[$tableName] = [];
                }

                if ($isPrimary) {
                    $identifiers[$tableName][$columnName] = $entity->{$getter}();
                } else {
                    $data[$tableName][$columnName] = $this->converter->toDatabaseType($entity->{$getter}(), $type);
                }
            } elseif ($config[0] === EntityMeta::TYPE_ONE_TO_MANY) {
                $relations[] = $this->getRelation($entity, $config);
            } else {
                throw new OrmException('Provided an invalid property type config');
            }
        }

        foreach ($data as $tableName => $values) {
            $this->connection->update($tableName, $values, $identifiers[$tableName] ?? throw new OrmException('No primary key exists for table ' . $tableName));
        }

        $this->handleRelations($entity, $relations);

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
                if (! isset($identifiers[$tableName])) {
                    $identifiers[$tableName] = [];
                }

                if ($isPrimary) {
                    $identifiers[$tableName][$columnName] = $entity->{$getter}();
                }
            } elseif ($config[0] === EntityMeta::TYPE_ONE_TO_MANY) {
                $relations[] = $this->getRelation($entity, $config);
            } else {
                throw new OrmException('Provided an invalid property type config');
            }
        }

        foreach ($identifiers as $tableName => $identifier) {
            $this->connection->delete($tableName, $identifier);
        }

        $sourcePrimaryId = $this->entityMeta->getPrimaryId($entity);

        foreach ($relations as $relation) {
            [$collection, $relationTable, $sourceColumn, $targetColumn, $types] = $relation;

            $this->connection->delete($relationTable, [$sourceColumn => $sourcePrimaryId]);
        }

        $this->connection->transactionCommit();
    }

    /**
     * @param list<mixed> $config
     *
     * @throws OrmException
     *
     * @return array{DoctrineCollection<int,EntityInterface>,string,string,string,list<class-string>}
     */
    private function getRelation(EntityInterface $entity, array $config): array
    {
        [$type, $class, $setter, $getter, $relationTable, $sourceColumn, $targetColumn, $types] = $config;

        $value = $entity->{$getter}();

        if ($value === null) {
            $value = new ArrayCollection();
        }

        if (! $value instanceof DoctrineCollection) {
            throw new OrmException('Provided one to many property must return a ' . DoctrineCollection::class);
        }

        return [$value, $relationTable, $sourceColumn, $targetColumn, $types];
    }

    /**
     * @param list<array{DoctrineCollection<int,EntityInterface>,string,string,string,list<class-string>}> $relations
     */
    private function handleRelations(EntityInterface $entity, array $relations): void
    {
        $sourcePrimaryId = $this->entityMeta->getPrimaryId($entity);

        foreach ($relations as $relation) {
            [$collection, $relationTable, $sourceColumn, $targetColumn, $types] = $relation;

            $this->connection->delete($relationTable, [$sourceColumn => $sourcePrimaryId]);
            foreach ($collection as $relationEntity) {
                $relationEntityId = $this->entityMeta->getPrimaryId($relationEntity);
                if (in_array($relationEntityId, [null, '', '0'], true)) {
                    $relationEntityId = $this->insert($relationEntity);
                }

                $this->connection->insert($relationTable, [
                    $sourceColumn => $sourcePrimaryId,
                    $targetColumn => $relationEntityId,
                ]);
            }
        }
    }

    private function generateSystemId(): string
    {
        return substr(sha1(uniqid()), 0, 20);
    }
}
