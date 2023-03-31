<?php

declare(strict_types=1);

namespace Artemeon\Orm\Condition;

use Artemeon\Orm\ConditionInterface;

use function sprintf;

class EmptyCondition implements ConditionInterface
{
    private string $columnName;
    private bool $negated;

    public function __construct(string $columnName, bool $negated = false)
    {
        $this->columnName = $columnName;
        $this->negated = $negated;
    }

    public function getParams(): array
    {
        return [];
    }

    public function getWhere(): string
    {
        if ($this->negated) {
            return sprintf('%s IS NOT NULL AND %s != ?', $this->columnName, '');
        } else {
            return sprintf('%s IS NULL OR %s = ?', $this->columnName, '');
        }
    }
}
