<?php

declare(strict_types=1);

namespace Artemeon\Orm\Condition;

use Artemeon\Orm\Comparator;
use Artemeon\Orm\ConditionInterface;

use function sprintf;

readonly class IsNullCondition implements ConditionInterface
{
    public function __construct(
        private string $columnName,
        private bool $negated = false,
    ) {
    }

    public function getParams(): array
    {
        return [];
    }

    public function getWhere(): string
    {
        return sprintf('%s %s', $this->columnName, $this->getComparator()->toSql());
    }

    private function getComparator(): Comparator
    {
        return $this->negated ? Comparator::IS_NOT_NULL : Comparator::IS_NULL;
    }
}
