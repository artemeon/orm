<?php

declare(strict_types=1);

namespace Artemeon\Orm;

use Artemeon\Database\ConnectionInterface;
use Artemeon\Orm\Exception\OrmException;

/**
 * The query builder creates a raw SQL select statement to fetch all fields.
 */
class QueryBuilder
{
    private array $blockedTableAlias = ['user'];

    public function __construct(
        private readonly ConnectionInterface $connection,
        private readonly EntityMeta $entityMeta,
    ) {
    }

    public function buildFrom(string $entityClass, ?string $joinColumn = null): string
    {
        $targetTables = $this->entityMeta->getTableNames($entityClass);
        if ($targetTables === []) {
            throw new OrmException('Entity ' . $entityClass . ' has no target table');
        }

        $firstPrimaryKey = null;
        $parts = [];
        foreach ($targetTables as $class => $tableName) {
            $primaryColumn = $this->entityMeta->getPrimaryColumn($class);
            $enclosedTable = $this->connection->encloseTableName($tableName);
            if ($firstPrimaryKey === null) {
                $firstPrimaryKey = $primaryColumn;
                if ($joinColumn !== null) {
                    $parts[] = 'INNER JOIN ' . $enclosedTable . ' AS ' . $enclosedTable . ' ON ' . $enclosedTable . '.' . $primaryColumn . ' = ' . $joinColumn;
                } else {
                    $parts[] = 'FROM ' . $enclosedTable . ' AS ' . $enclosedTable;
                }
            } elseif (in_array($tableName, $this->blockedTableAlias, true)) {
                $parts[] = 'INNER JOIN ' . $enclosedTable . ' ON ' . $primaryColumn . ' = ' . $firstPrimaryKey;
            } else {
                $parts[] = 'INNER JOIN ' . $enclosedTable . ' AS ' . $enclosedTable . ' ON ' . $enclosedTable . '.' . $primaryColumn . ' = ' . $firstPrimaryKey;
            }
        }

        return implode(' ', $parts);
    }
}
