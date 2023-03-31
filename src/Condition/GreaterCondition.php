<?php

declare(strict_types=1);

namespace Artemeon\Orm\Condition;

use Artemeon\Orm\ConditionInterface;

use function sprintf;

class GreaterCondition implements ConditionInterface
{
    private string $columnName;
    private mixed $value;
    private bool $inclusive;

    public function __construct(string $columnName, mixed $value, bool $inclusive = false)
    {
        $this->columnName = $columnName;
        $this->value = $value;
        $this->inclusive = $inclusive;
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
