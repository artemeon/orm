<?php

declare(strict_types=1);

namespace Artemeon\Orm\Condition;

use Artemeon\Orm\ConditionInterface;
use Artemeon\Orm\Conjunction;

/**
 * An orm condition to store several orm conditions.
 * They will connect via the given conjunction.
 */
class CompositeCondition implements ConditionInterface
{
    public function __construct(
        /**
         * @var ConditionInterface[]
         */
        private array $conditions = [],
        private Conjunction $conjunction = Conjunction::AND,
    ) {
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
        return $this->conditions !== [];
    }

    public function getWhere(): string
    {
        $where = [];
        foreach ($this->conditions as $condition) {
            $return = $condition->getWhere();
            if (! empty($return)) {
                $where[] = $return;
            }
        }

        $result = '';
        if ($where !== []) {
            $result = implode(') ' . $this->conjunction->toSql() . ' (', $where);
            $result = count($where) === 1 ? '(' . $result . ')' : '( (' . $result . ') )';
        }

        return $result;
    }

    public function getParams(): array
    {
        $params = [];
        foreach ($this->conditions as $condition) {
            $return = $condition->getWhere();
            if (! empty($return)) {
                $params = array_merge($params, $condition->getParams());
            }
        }

        return $params;
    }
}
