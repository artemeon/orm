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
    private array $type;
    private string $primaryValue;
    private ConnectionInterface $connection;
    private FieldMapper $mapper;
    private QueryBuilder $queryBuilder;

    public function __construct(string $relationTable, string $sourceColumn, string $targetColumn, array $type, string $primaryValue, ConnectionInterface $connection, FieldMapper $mapper, QueryBuilder $queryBuilder)
    {
        $this->relationTable = $relationTable;
        $this->sourceColumn = $sourceColumn;
        $this->targetColumn = $targetColumn;
        $this->type = $type;
        $this->primaryValue = $primaryValue;
        $this->connection = $connection;
        $this->mapper = $mapper;
        $this->queryBuilder = $queryBuilder;
    }

    protected function doInitialize(): void
    {
        $this->collection = new ArrayCollection();

        $from = $this->queryBuilder->buildFrom($this->type[0], 'rel.' . $this->sourceColumn);
        $query = 'SELECT * FROM ' . $this->relationTable . ' AS rel ' . $from . ' WHERE rel.' . $this->sourceColumn . ' = ?';

        $result = $this->connection->fetchAllAssociative($query, [$this->primaryValue]);
        $entityClass = $this->type[0];

        foreach ($result as $row) {
            $entity = new $entityClass();
            $this->mapper->map($entity, $row);

            $this->collection->add($entity);
        }
    }
}
