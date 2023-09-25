<?php

declare(strict_types=1);

namespace Artemeon\Orm\Tests;

use Artemeon\Database\Connection;
use Artemeon\Database\ConnectionInterface;
use Artemeon\Database\ConnectionParameters;
use Artemeon\Database\DriverFactory;
use Artemeon\Database\Schema\DataType;
use Artemeon\Orm\Converter;
use Artemeon\Orm\EntityManager;
use Artemeon\Orm\EntityMeta;
use Artemeon\Orm\FieldMapper;
use Artemeon\Orm\QueryBuilder;
use Artemeon\Orm\SchemaManager;
use Artemeon\Orm\Tests\FieldMapper\TestModel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

abstract class EntityManagerTestCase extends TestCase
{
    private static ?ConnectionInterface $connection = null;
    private static ?EntityManager $entityManager = null;
    private static ?SchemaManager $schemaManager = null;
    private static ?EntityMeta $entityMeta = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->flushDBCache();
        $this->setupFixture();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    protected function getConnection(): ConnectionInterface
    {
        if (self::$connection) {
            return self::$connection;
        }

        $host = getenv('DB_HOST') ?: 'localhost';
        $user = getenv('DB_USER') ?: 'test';
        $password = getenv('DB_PWD') ?: 'test';
        $database = getenv('DB_SCHEMA') ?: ':memory:';
        $port = getenv('DB_PORT') ? (int) getenv('DB_PORT') : null;
        $driver = getenv('DB_DRIVER') ?: 'sqlite3';

        $params = new ConnectionParameters($host, $user, $password, $database, $port, $driver);
        $factory = new DriverFactory();

        return self::$connection = new Connection($params, $factory);
    }

    protected function getEntityManager(): EntityManager
    {
        if (self::$entityManager) {
            return self::$entityManager;
        }

        $queryBuilder = new QueryBuilder($this->getConnection(), $this->getEntityMeta());
        $converter = new Converter();
        $fieldMapper = new FieldMapper($this->getEntityMeta(), $this->getConnection(), $converter);
        $entityManager = new EntityManager($this->getConnection(), $queryBuilder, $fieldMapper, $this->getEntityMeta(), $converter);

        return self::$entityManager = $entityManager;
    }

    protected function getSchemaManager(): SchemaManager
    {
        if (self::$schemaManager) {
            return self::$schemaManager;
        }

        $schemaManager = new SchemaManager($this->getConnection(), $this->getEntityMeta());

        return self::$schemaManager = $schemaManager;
    }

    protected function getEntityMeta(): EntityMeta
    {
        return self::$entityMeta ?: self::$entityMeta = new EntityMeta(new Psr16Cache(new ArrayAdapter()));
    }

    protected function flushDBCache()
    {
        $this->getConnection()->flushPreparedStatementsCache();
        $this->getConnection()->flushQueryCache();
        $this->getConnection()->flushTablesCache();
    }

    private function setupFixture()
    {
        $this->getConnection()->dropTable('agp_contracts_con');
        $this->getConnection()->dropTable('agp_contracts_con2foo');
        $this->getConnection()->dropTable('agp_system');

        $schemaManager = $this->getSchemaManager();
        $schemaManager->createTable(TestModel::class);

        $entityManager = $this->getEntityManager();

        for ($i = 1; $i <= 50; $i++) {
            $entity = new TestModel();
            $entity->setStrServicerId($this->generateSystemid());
            $entity->setIntInhouseService(1);
            $entity->setOutsourcingInstitution('foobar');
            $entity->setPurchasingRelevance(1);
            $entity->setOwner('foo');
            $entityManager->insert($entity);
        }
    }

    protected function generateSystemid(): string
    {
        return substr(sha1(uniqid()), 0, 20);
    }
}
