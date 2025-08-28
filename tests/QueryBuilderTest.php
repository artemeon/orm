<?php

declare(strict_types=1);

namespace Artemeon\Orm\Tests;

use Artemeon\Database\MockConnection;
use Artemeon\Orm\EntityMeta;
use Artemeon\Orm\Exception\OrmException;
use Artemeon\Orm\QueryBuilder;
use Artemeon\Orm\Tests\FieldMapper\TestInvalidModel;
use Artemeon\Orm\Tests\FieldMapper\TestModel;
use Artemeon\Orm\Tests\FieldMapper\TestModelWithTooLongRelation;
use Artemeon\Orm\Tests\FieldMapper\TestSubTestUser;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

/**
 * @internal
 */
class QueryBuilderTest extends TestCase
{
    public function testBuildFrom(): void
    {
        $connection = new MockConnection();
        $entityMeta = new EntityMeta(new Psr16Cache(new ArrayAdapter()));
        $queryBuilder = new QueryBuilder($connection, $entityMeta);

        $actual = $queryBuilder->buildFrom(TestModel::class);
        $expect = 'FROM agp_contracts_con AS agp_contracts_con INNER JOIN agp_system AS agp_system ON agp_system.system_id = contract_id';

        $this->assertSame($expect, $actual);
    }

    public function testInvalidEntity(): void
    {
        $this->expectException(OrmException::class);

        $connection = new MockConnection();
        $entityMeta = new EntityMeta(new Psr16Cache(new ArrayAdapter()));
        $queryBuilder = new QueryBuilder($connection, $entityMeta);

        $queryBuilder->buildFrom(TestInvalidModel::class);
    }

    public function testBlockedTableAlias(): void
    {
        $connection = new MockConnection();
        $entityMeta = new EntityMeta(new Psr16Cache(new ArrayAdapter()));
        $queryBuilder = new QueryBuilder($connection, $entityMeta);

        $actual = $queryBuilder->buildFrom(TestSubTestUser::class);
        $expect = 'FROM sub_user AS sub_user INNER JOIN user ON user_id = sub_user_id';

        $this->assertSame($expect, $actual);
    }

    public function testTooLongRelationName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The relation table name must be not larger than 30 characters');

        $connection = new MockConnection();
        $entityMeta = new EntityMeta(new Psr16Cache(new ArrayAdapter()));
        $queryBuilder = new QueryBuilder($connection, $entityMeta);

        $queryBuilder->buildFrom(TestModelWithTooLongRelation::class);
    }
}
