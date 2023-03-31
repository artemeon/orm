<?php

namespace Artemeon\Orm;

use Artemeon\Database\ConnectionInterface;

class EntityManager
{
    private ConnectionInterface $connection;
    private QueryBuilder $queryBuilder;
    private FieldMapper $fieldMapper;

    public function __construct(ConnectionInterface $connection, QueryBuilder $queryBuilder, FieldMapper $fieldMapper)
    {
        $this->connection = $connection;
        $this->queryBuilder = $queryBuilder;
        $this->fieldMapper = $fieldMapper;
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
            $query.= $condition->getStrWhere() . ' ';
            $params = array_merge($params, $condition->getArrParams());
        }

        if (count($sorting) > 0) {
            $query.= ' ORDER BY ';
            foreach ($sorting as $sort) {
                $query.= $sort->getStrOrderBy() . ' ';
            }
        }

        return [$query, $params];
    }
}
