<?php

namespace Artemeon\Orm;

enum Conjunction
{
    case AND;
    case OR;

    public function toSql(): string
    {
        switch ($this) {
            case self::AND:
                return 'AND';
            case self::OR:
                return 'OR';
        }

        throw new \RuntimeException('Invalid value');
    }
}
