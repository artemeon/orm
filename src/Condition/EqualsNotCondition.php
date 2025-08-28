<?php

declare(strict_types=1);

namespace Artemeon\Orm\Condition;

readonly class EqualsNotCondition extends EqualsCondition
{
    public function __construct(string $columnName, mixed $value)
    {
        parent::__construct($columnName, $value, true);
    }
}
