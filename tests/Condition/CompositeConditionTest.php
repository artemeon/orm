<?php

declare(strict_types=1);

namespace Artemeon\Orm\Tests\Condition;

use Artemeon\Orm\Condition\CompositeCondition;
use Artemeon\Orm\Condition\EqualsCondition;
use Artemeon\Orm\Conjunction;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class CompositeConditionTest extends TestCase
{
    public function testSingleCondition(): void
    {
        $condition = new CompositeCondition([
            new EqualsCondition('foo', 'bar'),
        ]);

        self::assertSame('(foo = ?)', $condition->getWhere());
        self::assertSame(['bar'], $condition->getParams());
    }

    public static function multipleConditionsProvider(): iterable
    {
        foreach (Conjunction::cases() as $conjunction) {
            yield [$conjunction];
        }
    }

    #[DataProvider('multipleConditionsProvider')]
    public function testMultipleConditions(Conjunction $conjunction): void
    {
        $condition = new CompositeCondition([
            new EqualsCondition('foo', 'bar'),
        ]);

        $condition->setConjunction($conjunction);

        $condition->addCondition(new EqualsCondition('baz', 'qux'));

        self::assertTrue($condition->hasConditions());
        self::assertSame('( (foo = ?) ' . $conjunction->toSql() . ' (baz = ?) )', $condition->getWhere());
        self::assertSame(['bar', 'qux'], $condition->getParams());
        self::assertSame($conjunction, $condition->getConjunction());
    }
}
