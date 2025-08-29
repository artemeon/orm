<?php

declare(strict_types=1);

namespace Artemeon\Orm\Condition;

use Artemeon\Orm\Comparator;
use Artemeon\Orm\ConditionInterface;

use function sprintf;

readonly class GreaterCondition implements ConditionInterface
{
    public function __construct(
        private string $columnName,
        private mixed $value,
        private bool $inclusive = false,
    ) {
    }

    public function getParams(): array
    {
        return [$this->value];
    }

    public function getWhere(): string
    {
        return sprintf('%s %s ?', $this->columnName, $this->getComparator()->toSql());
    }

    private function getComparator(): Comparator
    {
        return $this->inclusive ? Comparator::GREATER_THEN_EQUALS : Comparator::GREATER_THEN;
    }
}
