<?php

namespace Artemeon\Orm;

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
        switch ($this) {
            case self::GREATER_THEN:
                return '>';
            case self::GREATER_THEN_EQUALS:
                return '>=';
            case self::LESS_THEN:
                return '<';
            case self::LESS_THEN_EQUALS:
                return '<=';
            case self::EQUAL:
                return '=';
            case self::NOT_EQUAL:
                return '!=';
            case self::LIKE:
                return 'LIKE';
            case self::NOT_LIKE:
                return 'NOT LIKE';
            case self::IS_NULL:
                return 'IS NULL';
            case self::IS_NOT_NULL:
                return 'IS NOT NULL';
            case self::IN:
                return 'IN';
            case self::NOT_IN:
                return 'NOT IN';
        }

        throw new \RuntimeException('Invalid value');
    }
}
