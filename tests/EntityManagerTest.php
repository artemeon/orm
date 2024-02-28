<?php

namespace Artemeon\Orm\Tests;

use Artemeon\Orm\Collection;
use Artemeon\Orm\Condition\EqualsCondition;
use Artemeon\Orm\Tests\FieldMapper\TestModel;
use Artemeon\Orm\Tests\FieldMapper\TestParent;
use Doctrine\Common\Collections\ArrayCollection;

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

    public function testInsert()
    {
        $relation = new TestParent();
        $relation->setOwner('foobar');
        $collection = new ArrayCollection();
        $collection->add($relation);

        $entity = new TestModel();
        $entity->setStrServicerId($this->generateSystemid());
        $entity->setIntInhouseService(1);
        $entity->setOutsourcingInstitution('foobar');
        $entity->setPurchasingRelevance(1);
        $entity->setOwner('foo');
        $entity->setRelations($collection);
        $this->getEntityManager()->insert($entity);

        $this->assertNotEmpty($entity->getContractId());
    }

    public function testUpdate()
    {
        $entity = $this->getEntityManager()->findOne(TestModel::class, [new EqualsCondition('outsourcing_i', 'foobar')]);
        $this->assertInstanceOf(TestModel::class, $entity);

        $entity->setStrServicerId($this->generateSystemid());
        $entity->setIntInhouseService(1);
        $entity->setOutsourcingInstitution('foobar');
        $entity->setPurchasingRelevance(1);
        $entity->setOwner('bar');

        $this->getEntityManager()->update($entity);

        $this->assertNotEmpty($entity->getContractId());
    }

    public function testDelete()
    {
        $entity = $this->getEntityManager()->findOne(TestModel::class, [new EqualsCondition('outsourcing_i', 'foobar')]);
        $this->assertInstanceOf(TestModel::class, $entity);

        $this->getEntityManager()->delete($entity);

        $this->assertNotEmpty($entity->getContractId());
    }
}
