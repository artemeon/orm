<?php

declare(strict_types=1);

namespace Artemeon\Orm;

/**
 * A single order-by statement.
 * Pass them to the object-list-instance before loading the resultset.
 * Pass values using the syntax "column_mame ORDER". Don't add "ORDER BY" or commas since this
 * gets handled by the mapper.
 */
class OrderBy implements OrderByInterface
{
    public function __construct(
        private string $orderBy {
            set => $this->orderBy = ' ' . $value . ' ';
        },
    ) {
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
