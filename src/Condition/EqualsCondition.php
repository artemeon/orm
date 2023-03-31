<?php

declare(strict_types=1);

namespace Artemeon\Orm\Condition;

use Artemeon\Orm\ConditionInterface;

use function sprintf;

class EqualsCondition implements ConditionInterface
{
    private string $columnName;
    private mixed $value;
    private bool $negated;

    public function __construct(string $columnName, mixed $value, bool $negated = false)
    {
        $this->columnName = $columnName;
        $this->value = $value;
        $this->negated = $negated;
    }

    public function getParams(): array
    {
        return [$this->value];
    }

    public function getWhere(): string
    {
        if ($this->negated) {
            return sprintf('%s != ?', $this->columnName);
        } else {
            return sprintf('%s = ?', $this->columnName);
        }
    }
}
