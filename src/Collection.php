<?php

namespace Artemeon\Orm;

use Artemeon\Database\ConnectionInterface;
use Doctrine\Common\Collections\AbstractLazyCollection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * The orm assignment array is used to create a lazy loading way of handling object-assignments.
 * In most cases this is transparent, so there's no real usage of this class directly.
 *
 * @extends AbstractLazyCollection<string, EntityInterface>
 */
class Collection extends AbstractLazyCollection
{
    public function __construct(private readonly string $relationTable, private readonly string $sourceColumn, private array $type, private readonly string $primaryValue, private readonly ConnectionInterface $connection, private readonly FieldMapper $mapper, private readonly QueryBuilder $queryBuilder) {}

    protected function doInitialize(): void
    {
        $this->collection = new ArrayCollection;

        $from = $this->queryBuilder->buildFrom($this->type[0], 'rel.'.$this->sourceColumn);
        $query = 'SELECT * FROM '.$this->relationTable.' AS rel '.$from.' WHERE rel.'.$this->sourceColumn.' = ?';

        $result = $this->connection->fetchAllAssociative($query, [$this->primaryValue]);
        $entityClass = $this->type[0];

        foreach ($result as $row) {
            $entity = new $entityClass;
            $this->mapper->map($entity, $row);

            $this->collection->add($entity);
        }
    }
}
