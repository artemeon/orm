<?php

namespace Artemeon\Orm;

enum Conjunction
{
    case AND;
    case OR;

    public function toSql(): string
    {
        return match ($this) {
            self::AND => 'AND',
            default => 'OR',
        };
    }
}
