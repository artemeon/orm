<?php

declare(strict_types=1);

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
    protected string $where = '';

    /**
     * @var list<mixed>
     */
    protected array $params = [];

    /**
     * @param list<mixed> $params
     */
    public function __construct(string $where, array $params = [])
    {
        $this->setWhere($where);
        $this->setParams($params);
    }

    /**
     * @param list<mixed> $params
     */
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
        return $this->where === '' || $this->where === '0';
    }

    /**
     * Generic method to create an ORM restriction.
     */
    final public static function forValue(mixed $value, string $tableColumn, ?Comparator $comparator = null): ?ConditionInterface
    {
        if (is_string($value)) {
            if (!$comparator instanceof Comparator || $comparator === Comparator::LIKE) {
                return new LikeCondition($tableColumn, '%' . $value . '%');
            }

            return new Condition($tableColumn . ' ' . $comparator->toSql() . ' ?', [$value]);
        }

        if (is_int($value) || is_float($value)) {
            if (!$comparator instanceof Comparator || $comparator === Comparator::EQUAL) {
                return new EqualsCondition($tableColumn, $value);
            }

            return new Condition($tableColumn . ' ' . $comparator->toSql() . ' ?', [$value]);
        }

        if (is_bool($value)) {
            if (!$comparator instanceof Comparator || $comparator === Comparator::EQUAL) {
                return new EqualsCondition($tableColumn, $value ? 1 : 0);
            }

            return new Condition($tableColumn . ' ' . $comparator->toSql() . ' ?', [$value]);
        }

        if (null === $value) {
            return new IsNullCondition($tableColumn, $comparator === Comparator::IS_NOT_NULL);
        }

        if (is_array($value)) {
            if ($comparator === Comparator::IN_OR_EMPTY) {
                return new CompositeCondition([new InCondition($tableColumn, array_values($value)), new EmptyCondition($tableColumn)], Conjunction::OR);
            }

            if ($comparator === Comparator::NOT_IN_OR_EMPTY) {
                return new CompositeCondition([new InCondition($tableColumn, array_values($value), true), new EmptyCondition($tableColumn)], Conjunction::OR);
            }

            return new InCondition($tableColumn, array_values($value));
        }

        return null;
    }
}
