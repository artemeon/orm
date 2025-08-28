<?php

declare(strict_types=1);

namespace Artemeon\Orm\Tests\Condition;

use Artemeon\Orm\Condition\EqualsCondition;
use Artemeon\Orm\Condition\EqualsNotCondition;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class EqualsConditionTest extends TestCase
{
    public function testCondition(): void
    {
        $condition = new EqualsCondition('foo', 'bar');

        self::assertSame('foo = ?', $condition->getWhere());
        self::assertSame(['bar'], $condition->getParams());
    }

    public function testNegatedCondition(): void
    {
        $condition = new EqualsNotCondition('foo', 'bar');

        self::assertSame('foo != ?', $condition->getWhere());
        self::assertSame(['bar'], $condition->getParams());
    }
}
