<?php

namespace Artemeon\Orm;

use Artemeon\Orm\Condition\CompositeCondition;
use Artemeon\Orm\Condition\EmptyCondition;
use Artemeon\Orm\Condition\EqualsCondition;
use Artemeon\Orm\Condition\InCondition;
use Artemeon\Orm\Condition\IsNullCondition;
use Artemeon\Orm\Condition\LikeCondition;

/**
 * A orm condition may be used to create where restrictions for the objectList and objectCount queries.
 * Pass them using a syntax like "x = ?", don't add "WHERE", "AND", "OR" at the beginning, this is done by the mapper.
 */
class Condition implements ConditionInterface
{
    protected string $where = "";
    protected array $params = [];

    public function __construct(string $where, array $params = [])
    {
        $this->setWhere($where);
        $this->setParams($params);
    }

    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function setWhere(string $where): void
    {
        $this->where = trim($where);
    }

    public function getWhere(): string
    {
        return $this->where;
    }

    public function isEmpty(): bool
    {
        return empty($this->where);
    }

    /**
     * Generic method to create an ORM restriction.
     */
    final public static function forValue(mixed $value, string $tableColumn, ?Comparator $comparator = null): ?ConditionInterface
    {
        if (is_string($value)) {
            if ($comparator === null || $comparator === Comparator::LIKE) {
                return new LikeCondition($tableColumn, '%' . $value . '%');
            } else {
                return new Condition($tableColumn . ' ' . $comparator->toSql() . ' ?', [$value]);
            }
        } elseif (is_int($value) || is_float($value)) {
            if ($comparator === null || $comparator === Comparator::EQUAL) {
                return new EqualsCondition($tableColumn, $value);
            } else {
                return new Condition($tableColumn . ' ' . $comparator->toSql() . ' ?', [$value]);
            }
        } elseif (is_bool($value)) {
            if ($comparator === null || $comparator === Comparator::EQUAL) {
                return new EqualsCondition($tableColumn, $value ? 1 : 0);
            } else {
                return new Condition($tableColumn . ' ' . $comparator->toSql() . ' ?', [$value]);
            }
        } elseif (is_null($value)) {
            return new IsNullCondition($tableColumn, $comparator === Comparator::IS_NOT_NULL);
        } elseif (is_array($value)) {
            if ($comparator === Comparator::IN_OR_EMPTY) {
                return new CompositeCondition([new InCondition($tableColumn, $value), new EmptyCondition($tableColumn)], Conjunction::OR);
            } elseif ($comparator === Comparator::NOT_IN_OR_EMPTY) {
                return new CompositeCondition([new InCondition($tableColumn, $value, true), new EmptyCondition($tableColumn)], Conjunction::OR);
            }
            return new InCondition($tableColumn, $value);
        }

        return null;
    }
}
