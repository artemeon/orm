<?php

declare(strict_types=1);

namespace Artemeon\Orm\Condition;

use Artemeon\Orm\ConditionInterface;

use function sprintf;

class GreaterCondition implements ConditionInterface
{
    public function __construct(private readonly string $columnName, private readonly mixed $value, private readonly bool $inclusive = false)
    {
    }

    public function getParams(): array
    {
        return [$this->value];
    }

    public function getWhere(): string
    {
        if ($this->inclusive) {
            return sprintf('%s >= ?', $this->columnName);
        } else {
            return sprintf('%s > ?', $this->columnName);
        }
    }
}
