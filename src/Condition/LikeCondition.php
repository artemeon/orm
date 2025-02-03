<?php

declare(strict_types=1);

namespace Artemeon\Orm\Condition;

use Artemeon\Orm\ConditionInterface;

use function sprintf;

class LikeCondition implements ConditionInterface
{
    public function __construct(private readonly string $columnName, private readonly mixed $value, private readonly bool $negated = false)
    {
    }

    public function getParams(): array
    {
        return [$this->value];
    }

    public function getWhere(): string
    {
        if ($this->negated) {
            return sprintf('%s NOT LIKE ?', $this->columnName);
        }

        return sprintf('%s LIKE ?', $this->columnName);
    }
}
