<?php

namespace Artemeon\Orm;

/**
 * A single order-by statement.
 * Pass them to the objectlist-instance before loading the resultset.
 * Pass values using the syntax "columnmame ORDER". Don't add "ORDER BY" or commas since this
 * will be done by the mapper.
 */
class OrderBy implements OrderByInterface
{
    private string $orderBy;

    public function __construct(string $orderBy)
    {
        $this->orderBy = " ".$orderBy." ";
    }

    public function setOrderBy(string $orderBy): void
    {
        $this->orderBy = $orderBy;
    }

    public function getOrderBy(): string
    {
        return $this->orderBy;
    }
}
