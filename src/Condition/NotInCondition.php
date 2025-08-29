<?php

declare(strict_types=1);

namespace Artemeon\Orm\Condition;

readonly class NotInCondition extends InCondition
{
    public function __construct(string $columnName, array $params)
    {
        parent::__construct($columnName, $params, true);
    }
}
