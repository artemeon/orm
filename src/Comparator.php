<?php

declare(strict_types=1);

namespace Artemeon\Orm;

use RuntimeException;

enum Comparator
{
    case GREATER_THEN;
    case GREATER_THEN_EQUALS;
    case LESS_THEN;
    case LESS_THEN_EQUALS;
    case EQUAL;
    case NOT_EQUAL;
    case LIKE;
    case NOT_LIKE;
    case IS_NULL;
    case IS_NOT_NULL;
    case IN;
    case NOT_IN;
    case IN_OR_EMPTY;
    case NOT_IN_OR_EMPTY;

    public function toSql(): string
    {
        return match ($this) {
            self::GREATER_THEN => '>',
            self::GREATER_THEN_EQUALS => '>=',
            self::LESS_THEN => '<',
            self::LESS_THEN_EQUALS => '<=',
            self::EQUAL => '=',
            self::NOT_EQUAL => '!=',
            self::LIKE => 'LIKE',
            self::NOT_LIKE => 'NOT LIKE',
            self::IS_NULL => 'IS NULL',
            self::IS_NOT_NULL => 'IS NOT NULL',
            self::IN => 'IN',
            self::NOT_IN => 'NOT IN',
            default => throw new RuntimeException('Invalid value'),
        };
    }
}
