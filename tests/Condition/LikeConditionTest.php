<?php

declare(strict_types=1);

namespace Artemeon\Orm\Tests\Condition;

use Artemeon\Orm\Condition\LikeCondition;
use Artemeon\Orm\Condition\NotLikeCondition;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class LikeConditionTest extends TestCase
{
    public function testCondition(): void
    {
        $condition = new LikeCondition('foo', '%bar%');

        self::assertSame('foo LIKE ?', $condition->getWhere());
        self::assertSame(['%bar%'], $condition->getParams());
    }

    public function testNegatedCondition(): void
    {
        $condition = new NotLikeCondition('foo', '%bar%');

        self::assertSame('foo NOT LIKE ?', $condition->getWhere());
        self::assertSame(['%bar%'], $condition->getParams());
    }
}
