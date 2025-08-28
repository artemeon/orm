<?php

declare(strict_types=1);

namespace Artemeon\Orm\Tests\Condition;

use Artemeon\Orm\Condition\InCondition;
use Artemeon\Orm\Condition\NotInCondition;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class InConditionTest extends TestCase
{
    public function testCondition(): void
    {
        $condition = new InCondition('foo', ['bar', 'baz']);

        self::assertSame('foo IN (?,?)', $condition->getWhere());
        self::assertSame(['bar', 'baz'], $condition->getParams());
    }

    public function testNegatedCondition(): void
    {
        $condition = new NotInCondition('foo', ['bar', 'baz']);

        self::assertSame('foo NOT IN (?,?)', $condition->getWhere());
        self::assertSame(['bar', 'baz'], $condition->getParams());
    }

    public function testManyValues(): void
    {
        $range = range(1, InCondition::MAX_IN_VALUES + 1);

        $condition = new InCondition('foo', $range);

        $first = implode(',', array_map(static fn (int $step): string => '?', range(1, InCondition::MAX_IN_VALUES)));
        $second = implode(',', array_map(static fn (int $step): string => '?', range(InCondition::MAX_IN_VALUES + 1, InCondition::MAX_IN_VALUES + 1)));

        self::assertSame('(foo IN (' . $first . ') OR foo IN (' . $second . '))', $condition->getWhere());
        self::assertSame($range, $condition->getParams());
    }

    public function testEmptyValues(): void
    {
        $condition = new InCondition('foo', []);

        self::assertSame('', $condition->getWhere());
    }
}
