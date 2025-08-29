<?php

declare(strict_types=1);

namespace Artemeon\Orm\Condition;

use Artemeon\Orm\Comparator;
use Artemeon\Orm\ConditionInterface;
use Artemeon\Orm\Conjunction;

use function sprintf;

readonly class EmptyCondition implements ConditionInterface
{
    public function __construct(
        private string $columnName,
        private bool $negated = false,
    ) {
    }

    public function getParams(): array
    {
        return [''];
    }

    public function getWhere(): string
    {
        if ($this->negated) {
            return sprintf(
                '%s %s %s %s %s ?',
                $this->columnName,
                Comparator::IS_NOT_NULL->toSql(),
                Conjunction::AND->toSql(),
                $this->columnName,
                Comparator::NOT_EQUAL->toSql(),
            );
        }

        return sprintf(
            '%s %s %s %s %s ?',
            $this->columnName,
            Comparator::IS_NULL->toSql(),
            Conjunction::OR->toSql(),
            $this->columnName,
            Comparator::EQUAL->toSql(),
        );
    }
}
