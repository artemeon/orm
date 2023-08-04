<?php

namespace Artemeon\Orm\Tests;

use Artemeon\Orm\Condition\EqualsCondition;
use Artemeon\Orm\Tests\FieldMapper\TestModel;

class EntityManagerTest extends EntityManagerTestCase
{
    public function testFindAll()
    {
        $result = $this->getEntityManager()->findAll(TestModel::class);

        $this->assertEquals(50, count($result));
    }

    public function testFind()
    {
        $result = $this->getEntityManager()->findOne(TestModel::class, [new EqualsCondition('outsourcing_i', 'foobar')]);

        $this->assertInstanceOf(TestModel::class, $result);
    }
}
