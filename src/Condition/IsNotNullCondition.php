<?php

declare(strict_types=1);

namespace Artemeon\Orm\Condition;

readonly class IsNotNullCondition extends IsNullCondition
{
    public function __construct(string $columnName)
    {
        parent::__construct($columnName, true);
    }
}
