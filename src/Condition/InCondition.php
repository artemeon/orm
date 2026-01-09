<?php

declare(strict_types=1);

namespace Artemeon\Orm\Condition;

use Artemeon\Orm\Comparator;
use Artemeon\Orm\ConditionInterface;
use Artemeon\Orm\Conjunction;

/**
 * A orm condition may be used to create where conditions for the objectList and objectCount queries.
 * This condition creates an IN statement e.g. "AND <columnname> IN (<parameters>)".
 */
readonly class InCondition implements ConditionInterface
{
    /**
     * @internal
     */
    public const int MAX_IN_VALUES = 950;

    /**
     * @param list<mixed> $params
     */
    public function __construct(
        private string $columnName,
        private array $params,
        private bool $negated = false,
    ) {
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function getWhere(): string
    {
        return $this->getInStatement($this->columnName);
    }

    protected function getInStatement(string $columnName): string
    {
        if ($this->params === []) {
            return '';
        }

        $operator = $this->negated ? Comparator::NOT_IN : Comparator::IN;

        if (count($this->params) > self::MAX_IN_VALUES) {
            $count = ceil(count($this->params) / self::MAX_IN_VALUES);
            $parts = [];

            for ($i = 0; $i < $count; $i++) {
                $params = array_slice($this->params, $i * self::MAX_IN_VALUES, self::MAX_IN_VALUES);
                $paramsPlaceholder = array_map(static fn (mixed $value): string => '?', $params);
                $placeholder = implode(',', $paramsPlaceholder);
                if ($placeholder !== '') {
                    $parts[] = sprintf('%s %s (%s)', $columnName, $operator->toSql(), $placeholder);
                }
            }

            if ($parts !== []) {
                return '(' . implode(' ' . Conjunction::OR->toSql() . ' ', $parts) . ')';
            }
        } else {
            $placeholder = implode(',', array_map(static fn (mixed $value): string => '?', $this->params));

            return sprintf('%s %s (%s)', $columnName, $operator->toSql(), $placeholder);
        }

        return '';
    }
}
