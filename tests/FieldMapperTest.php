<?php

namespace Artemeon\Orm\Tests;

use Artemeon\Database\MockConnection;
use Artemeon\Orm\Converter;
use Artemeon\Orm\EntityInterface;
use Artemeon\Orm\EntityMeta;
use Artemeon\Orm\FieldMapper;
use Artemeon\Orm\Tests\FieldMapper\TestModel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

class FieldMapperTest extends TestCase
{
    /**
     * @dataProvider mapDataProvider
     */
    public function testMap(EntityInterface $entity, array $row, array $expects): void
    {
        $meta = new EntityMeta(new Psr16Cache(new ArrayAdapter()));
        $mapper = new FieldMapper($meta, new MockConnection(), new Converter());
        $mapper->map($entity, $row);

        foreach ($expects as $getter => $expect) {
            self::assertSame($expect, $entity->{$getter}());
        }
    }

    public function mapDataProvider(): array
    {
        return [
            [
                new TestModel(),
                [
                    'servicerid' => 'foo',
                    'inhouseservice' => '8',
                    'outsourcing_i' => 'bar',
                    'purchasing_relevance' => 16,
                    'system_id' => 'test',
                    'owner' => 'owner',
                ],
                [
                    'getStrServicerId' => 'foo',
                    'getIntInhouseService' => 8,
                    'getOutsourcingInstitution' => 'bar',
                    'getPurchasingRelevance' => 16,
                    'getSystemId' => 'test',
                    'getOwner' => 'owner',
                ]
            ]
        ];
    }
}
