<?php

declare(strict_types=1);

namespace Artemeon\Orm\Condition;

readonly class NotEmptyCondition extends EmptyCondition
{
    public function __construct(string $columnName)
    {
        parent::__construct($columnName, true);
    }
}
