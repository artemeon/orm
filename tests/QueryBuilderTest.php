<?php

namespace Artemeon\Orm\Tests;

use Artemeon\Database\MockConnection;
use Artemeon\Orm\EntityMeta;
use Artemeon\Orm\QueryBuilder;
use Artemeon\Orm\Tests\FieldMapper\TestModel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

class QueryBuilderTest extends TestCase
{
    public function testBuildFrom()
    {
        $connection = new MockConnection();
        $entityMeta = new EntityMeta(new Psr16Cache(new ArrayAdapter()));
        $queryBuilder = new QueryBuilder($connection, $entityMeta);

        $actual = $queryBuilder->buildFrom(TestModel::class);
        $expect = <<<SQL
FROM agp_contracts_con AS agp_contracts_con INNER JOIN agp_system AS agp_system ON agp_system.system_id = contract_id
SQL;

        $this->assertEquals($expect, $actual);
    }
}
