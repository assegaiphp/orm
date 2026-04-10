<?php

namespace Tests\PHPUnit\Unit;

use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\DataSource\DataSourceOptions;
use Assegai\Orm\DataSource\SQLCharacterSet;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Exceptions\DataSourceException;
use Assegai\Orm\Management\DatabaseManager;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;

final class DatabaseManagerSqlTest extends TestCase
{
    public function testBuildsMysqlCreateDatabaseStatementsWithCharsetDefaults(): void
    {
        $sql = DatabaseManager::buildCreateDatabaseStatement(
            DataSourceType::MYSQL,
            'assegai_blog',
            SQLCharacterSet::UTF8MB4,
        );

        self::assertSame(
            'CREATE DATABASE IF NOT EXISTS `assegai_blog` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci',
            $sql,
        );
    }

    public function testBuildsMysqlDropDatabaseStatementsSafely(): void
    {
        $sql = DatabaseManager::buildDropDatabaseStatement(DataSourceType::MYSQL, 'assegai_blog');

        self::assertSame('DROP DATABASE IF EXISTS `assegai_blog`', $sql);
    }

    public function testBuildsPostgreSqlDatabaseStatementsSafely(): void
    {
        $createSql = DatabaseManager::buildCreateDatabaseStatement(DataSourceType::POSTGRESQL, 'assegai_blog');
        $dropSql = DatabaseManager::buildDropDatabaseStatement(DataSourceType::POSTGRESQL, 'assegai_blog');

        self::assertSame('CREATE DATABASE "assegai_blog"', $createSql);
        self::assertSame('DROP DATABASE IF EXISTS "assegai_blog"', $dropSql);
    }

    public function testRejectsUnsafeDatabaseNames(): void
    {
        $this->expectException(DataSourceException::class);
        $this->expectExceptionMessage('Unsafe database name: blog; DROP DATABASE mysql');

        DatabaseManager::buildCreateDatabaseStatement(DataSourceType::MYSQL, 'blog; DROP DATABASE mysql');
    }

    public function testCreatesDropsAndDetectsSqliteDatabaseFiles(): void
    {
        $path = sys_get_temp_dir() . '/assegai-dbman-' . uniqid('', true) . '.sqlite';
        @unlink($path);

        $dataSource = new DataSource(new DataSourceOptions(
            entities: [],
            name: $path,
            type: DataSourceType::SQLITE,
            path: $path,
        ));
        $dataSource->disconnect();

        $manager = DatabaseManager::getInstance();

        try {
            clearstatcache();
            self::assertTrue(file_exists($path));
            self::assertTrue($manager->exists($dataSource, $path));

            $manager->drop($dataSource, $path);
            clearstatcache();
            self::assertFalse(file_exists($path));
            self::assertFalse($manager->exists($dataSource, $path));

            $manager->setup($dataSource, $path);
            clearstatcache();
            self::assertTrue(file_exists($path));
            self::assertTrue($manager->exists($dataSource, $path));
        } finally {
            @unlink($path);
        }
    }

    public function testPostgreSqlSetupWrapsManagementConnectionFailures(): void
    {
        if (!extension_loaded('pdo_pgsql')) {
            self::markTestSkipped('pdo_pgsql is required for PostgreSQL management tests.');
        }

        $manager = DatabaseManager::getInstance();
        $dataSource = $this->createDetachedDataSource(new DataSourceOptions(
            entities: [],
            name: 'assegai_blog',
            type: DataSourceType::POSTGRESQL,
            host: '127.0.0.1',
            port: 1,
            username: 'postgres',
            password: 'postgres',
        ));

        $this->expectException(DataSourceException::class);
        $this->expectExceptionMessage('Data Source error:');

        $manager->setup($dataSource, 'assegai_blog');
    }

    public function testPostgreSqlDropWrapsManagementConnectionFailures(): void
    {
        if (!extension_loaded('pdo_pgsql')) {
            self::markTestSkipped('pdo_pgsql is required for PostgreSQL management tests.');
        }

        $manager = DatabaseManager::getInstance();
        $dataSource = $this->createDetachedDataSource(new DataSourceOptions(
            entities: [],
            name: 'assegai_blog',
            type: DataSourceType::POSTGRESQL,
            host: '127.0.0.1',
            port: 1,
            username: 'postgres',
            password: 'postgres',
        ));

        $this->expectException(DataSourceException::class);
        $this->expectExceptionMessage('Data Source error:');

        $manager->drop($dataSource, 'assegai_blog');
    }

    private function createDetachedDataSource(DataSourceOptions $options): DataSource
    {
        $reflection = new ReflectionClass(DataSource::class);
        /** @var DataSource $dataSource */
        $dataSource = $reflection->newInstanceWithoutConstructor();

        $this->setProperty($dataSource, 'options', $options);
        $this->setProperty($dataSource, 'type', $options->type);
        $this->setProperty($dataSource, 'entities', $options->entities);

        return $dataSource;
    }

    private function setProperty(object $object, string $propertyName, mixed $value): void
    {
        $property = new ReflectionProperty($object, $propertyName);
        $property->setValue($object, $value);
    }
}
