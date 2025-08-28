<?php

declare(strict_types=1);

namespace Artemeon\Orm\Tests\Condition;

use Artemeon\Orm\Condition\GreaterCondition;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class GreaterConditionTest extends TestCase
{
    public function testCondition(): void
    {
        $condition = new GreaterCondition('foo', 'bar');

        self::assertSame('foo > ?', $condition->getWhere());
        self::assertSame(['bar'], $condition->getParams());
    }

    public function testInclusiveCondition(): void
    {
        $condition = new GreaterCondition('foo', 'bar', true);

        self::assertSame('foo >= ?', $condition->getWhere());
        self::assertSame(['bar'], $condition->getParams());
    }
}
