<?php

namespace Artemeon\Orm\Condition;

use Artemeon\Orm\Condition;
use Artemeon\Orm\Exception\OrmException;

/**
 * A orm condition may be used to create where conditions for the objectList and objectCount queries.
 * This condition creates an IN statement e.g. "AND <columnname> IN (<parameters>)"
 */
class InCondition extends Condition
{
    /**
     * @internal
     */
    public const MAX_IN_VALUES = 950;

    public function __construct(protected string $columnName, array $params, protected bool $negated = false)
    {
        parent::__construct('', $params);
    }

    /**
     * @throws OrmException
     */
    #[\Override]
    public function setParams(array $params): void
    {
        throw new OrmException('Setting params for property IN restrictions is not supported');
    }

    /**
     * @throws OrmException
     */
    #[\Override]
    public function setWhere(string $where): void
    {
        throw new OrmException('Setting a where restriction for property IN restrictions is not supported');
    }

    /**
     * Here comes the magic, generation a where restriction out of the passed property name and the comparator
     */
    #[\Override]
    public function getWhere(): string
    {
        return $this->getInStatement($this->columnName);
    }

    protected function getInStatement(string $columnName): string
    {
        if (count($this->params) === 0) {
            return '';
        }

        $operator = $this->negated ? 'NOT IN' : 'IN';

        if (count($this->params) > self::MAX_IN_VALUES) {
            $count = ceil(count($this->params) / self::MAX_IN_VALUES);
            $parts = [];

            for ($i = 0; $i < $count; $i++) {
                $params = array_slice($this->params, $i * self::MAX_IN_VALUES, self::MAX_IN_VALUES);
                $paramsPlaceholder = array_map(static fn($value) => '?', $params);
                $placeholder = implode(',', $paramsPlaceholder);
                if (!empty($placeholder)) {
                    $parts[] = "{$columnName} {$operator} ({$placeholder})";
                }
            }

            if (count($parts) > 0) {
                return '(' . implode(' OR ', $parts) . ')';
            }
        } else {
            $placeholder = trim(str_repeat('?,', count($this->params)), ',');

            if ($placeholder !== '' && $placeholder !== '0') {
                return "{$columnName} {$operator} ({$placeholder})";
            }
        }

        return "";
    }
}
