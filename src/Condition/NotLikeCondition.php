<?php

declare(strict_types=1);

namespace Artemeon\Orm\Condition;

readonly class NotLikeCondition extends LikeCondition
{
    public function __construct(string $columnName, mixed $value)
    {
        parent::__construct($columnName, $value, true);
    }
}
