<?php

declare(strict_types=1);

namespace Artemeon\Orm\Condition;

use Artemeon\Orm\Comparator;
use Artemeon\Orm\ConditionInterface;

use function sprintf;

readonly class EqualsCondition implements ConditionInterface
{
    public function __construct(
        private string $columnName,
        private mixed $value,
        private bool $negated = false,
    ) {
    }

    public function getParams(): array
    {
        return [$this->value];
    }

    public function getWhere(): string
    {
        if ($this->negated) {
            return sprintf(
                '%s %s ?',
                $this->columnName,
                Comparator::NOT_EQUAL->toSql(),
            );
        }

        return sprintf(
            '%s %s ?',
            $this->columnName,
            Comparator::EQUAL->toSql(),
        );
    }
}
