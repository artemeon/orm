<?php

namespace Artemeon\Orm\Condition;

use Artemeon\Orm\ConditionInterface;
use Artemeon\Orm\Conjunction;

/**
 * A orm condition to to store several orm conditions.
 * They will connected via given condition connect.
 * e.g.
 *  ( (<restriction_1>) AND (<restriction_2>) AND (<restriction_3>) )
 *  ( (<restriction_1>) OR (<restriction_2>) OR (<restriction_3>) )
 */
class CompositeCondition implements ConditionInterface
{
    /**
     * @var ConditionInterface[]
     */
    private array $conditions;
    private Conjunction $conjunction;

    public function __construct(array $conditions = [], Conjunction $conjunction = Conjunction::AND)
    {
        $this->conditions = $conditions;
        $this->conjunction = $conjunction;
    }

    public function getConjunction(): Conjunction
    {
        return $this->conjunction;
    }

    public function setConjunction(Conjunction $conjunction): self
    {
        $this->conjunction = $conjunction;
        return $this;
    }

    public function addCondition(ConditionInterface $condition): self
    {
        $this->conditions[] = $condition;
        return $this;
    }

    public function hasConditions(): bool
    {
        return count($this->conditions) > 0;
    }

    public function getWhere(): string
    {
        $where = [];
        foreach ($this->conditions as $condition) {
            $return = $condition->getWhere();
            if (!empty($return)) {
                $where[] = $return;
            }
        }

        $result = '';
        if (count($where) > 0) {
            $result = implode(') ' . $this->conjunction->toSql() . ' (', $where);
            if (count($where) == 1) {
                $result = '(' . $result . ')';
            } else {
                $result = '( (' . $result . ') )';
            }
        }

        return $result;
    }

    public function getParams(): array
    {
        $params = [];
        foreach ($this->conditions as $condition) {
            $return = $condition->getWhere();
            if (!empty($return)) {
                $params = array_merge($params, $condition->getParams());
            }
        }

        return $params;
    }
}
