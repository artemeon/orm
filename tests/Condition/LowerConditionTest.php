<?php

declare(strict_types=1);

namespace Artemeon\Orm\Tests\Condition;

use Artemeon\Orm\Condition\LowerCondition;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class LowerConditionTest extends TestCase
{
    public function testCondition(): void
    {
        $condition = new LowerCondition('foo', 'bar');

        self::assertSame('foo < ?', $condition->getWhere());
        self::assertSame(['bar'], $condition->getParams());
    }

    public function testInclusiveCondition(): void
    {
        $condition = new LowerCondition('foo', 'bar', true);

        self::assertSame('foo <= ?', $condition->getWhere());
        self::assertSame(['bar'], $condition->getParams());
    }
}
