<?php

declare(strict_types=1);

namespace Artemeon\Orm\Tests\Condition;

use Artemeon\Orm\Condition\IsNotNullCondition;
use Artemeon\Orm\Condition\IsNullCondition;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class IsNullConditionTest extends TestCase
{
    public function testCondition(): void
    {
        $condition = new IsNullCondition('foo');

        self::assertSame('foo IS NULL', $condition->getWhere());
        self::assertSame([], $condition->getParams());
    }

    public function testNegatedCondition(): void
    {
        $condition = new IsNotNullCondition('foo');

        self::assertSame('foo IS NOT NULL', $condition->getWhere());
        self::assertSame([], $condition->getParams());
    }
}
