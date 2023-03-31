<?php

namespace Artemeon\Orm;

/**
 * A single order-by statement.
 * Pass them to the objectlist-instance before loading the resultset.
 * Pass values using the syntax "columnmame ORDER". Don't add "ORDER BY" or commas since this
 * will be done by the mapper.
 */
interface OrderByInterface
{
    public function getOrderBy(): string;
}
