<?php

namespace Artemeon\Orm;

use Artemeon\Database\ConnectionInterface;
use Doctrine\Common\Collections\AbstractLazyCollection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * The orm assignment array is used to create a lazy loading way of handling object-assignments.
 * In most cases this is transparent, so there's no real usage of this class directly.
 */
class Collection extends AbstractLazyCollection
{
    private string $relationTable;
    private string $sourceColumn;
    private string $targetColumn;
    private string $targetClass;
    private string $primaryValue;
    private ConnectionInterface $connection;
    private FieldMapper $mapper;
    private QueryBuilder $queryBuilder;

    public function __construct(string $relationTable, string $sourceColumn, string $targetColumn, string $targetClass, string $primaryValue, ConnectionInterface $connection, FieldMapper $mapper, QueryBuilder $queryBuilder)
    {
        $this->relationTable = $relationTable;
        $this->sourceColumn = $sourceColumn;
        $this->targetColumn = $targetColumn;
        $this->targetClass = $targetClass;
        $this->primaryValue = $primaryValue;
        $this->connection = $connection;
        $this->mapper = $mapper;
        $this->queryBuilder = $queryBuilder;
    }

    protected function doInitialize(): void
    {
        $this->collection = new ArrayCollection();

        $from = $this->queryBuilder->buildFrom($this->targetClass, $this->targetColumn);
        $query = 'SELECT * FROM ' . $this->relationTable . ' ' . $from . ' WHERE ' . $this->sourceColumn . ' = ?';

        $result = $this->connection->fetchAllAssociative($query, [$this->primaryValue]);
        $entityClass = $this->targetClass;

        foreach ($result as $row) {
            $entity = new $entityClass();
            $this->mapper->map($entity, $row);

            $this->collection->add($entity);
        }
    }
}
