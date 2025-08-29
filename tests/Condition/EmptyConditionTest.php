<?php

declare(strict_types=1);

namespace Artemeon\Orm\Tests\Condition;

use Artemeon\Orm\Condition\EmptyCondition;
use Artemeon\Orm\Condition\NotEmptyCondition;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class EmptyConditionTest extends TestCase
{
    public function testCondition(): void
    {
        $condition = new EmptyCondition('foo');

        self::assertSame('foo IS NULL OR foo = ?', $condition->getWhere());
        self::assertSame([''], $condition->getParams());
    }

    public function testNegatedCondition(): void
    {
        $condition = new NotEmptyCondition('foo');

        self::assertSame('foo IS NOT NULL AND foo != ?', $condition->getWhere());
        self::assertSame([''], $condition->getParams());
    }
}
