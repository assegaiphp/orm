<?php

namespace Tests\PHPUnit\Unit;

use Assegai\Orm\Attributes\Entity;
use Assegai\Orm\Attributes\SqlEntityOptions;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Enumerations\SQLDialect;
use PHPUnit\Framework\TestCase;

final class EntityAttributeTest extends TestCase
{
    public function testDriverDefaultsToNull(): void
    {
        $entity = new Entity();

        self::assertNull($entity->driver);
    }

    public function testExplicitDriverIsPreserved(): void
    {
        $entity = new Entity(driver: DataSourceType::MSSQL);

        self::assertSame(DataSourceType::MSSQL, $entity->driver);
    }

    public function testDataSourceNamePrefersExplicitDataSourceAlias(): void
    {
        $entity = new Entity(dataSource: 'primary', database: 'legacy');

        self::assertSame('primary', $entity->dataSourceName());
    }

    public function testDataSourceNameFallsBackToLegacyDatabaseProperty(): void
    {
        $entity = new Entity(database: 'legacy');

        self::assertSame('legacy', $entity->dataSourceName());
    }

    public function testEngineOnlyAppliesToMySqlFamilyDialects(): void
    {
        $entity = new Entity(engine: 'MyISAM');

        self::assertSame('MyISAM', $entity->engineForDialect(SQLDialect::MYSQL));
        self::assertSame('MyISAM', $entity->engineForDialect(DataSourceType::MARIADB));
        self::assertNull($entity->engineForDialect(SQLDialect::SQLITE));
        self::assertNull($entity->engineForDialect(SQLDialect::POSTGRESQL));
    }

    public function testSchemaOnlyAppliesToSchemaAwareDialects(): void
    {
        $entity = new Entity(schema: 'reporting');

        self::assertSame('reporting', $entity->schemaForDialect(SQLDialect::POSTGRESQL));
        self::assertSame('reporting', $entity->schemaForDialect(DataSourceType::MSSQL));
        self::assertNull($entity->schemaForDialect(SQLDialect::MYSQL));
        self::assertNull($entity->schemaForDialect(SQLDialect::SQLITE));
    }

    public function testWithoutRowIdOnlyAppliesToSqliteWhenExplicitlyConfigured(): void
    {
        self::assertNull((new Entity())->withoutRowIdForDialect(SQLDialect::SQLITE));
        self::assertTrue((new Entity(withRowId: false))->withoutRowIdForDialect(SQLDialect::SQLITE));
        self::assertFalse((new Entity(withRowId: true))->withoutRowIdForDialect(SQLDialect::SQLITE));
        self::assertNull((new Entity(withRowId: false))->withoutRowIdForDialect(SQLDialect::MYSQL));
    }

    public function testSqlEntityOptionsOnlyApplyOnSupportedDialects(): void
    {
        $sqlOptions = new SqlEntityOptions(engine: 'MyISAM', schema: 'reporting', withRowId: false);

        self::assertSame('MyISAM', $sqlOptions->engineForDialect(SQLDialect::MYSQL));
        self::assertSame('reporting', $sqlOptions->schemaForDialect(SQLDialect::POSTGRESQL));
        self::assertTrue($sqlOptions->withoutRowIdForDialect(SQLDialect::SQLITE));
        self::assertNull($sqlOptions->engineForDialect(SQLDialect::MSSQL));
        self::assertNull($sqlOptions->schemaForDialect(SQLDialect::MYSQL));
    }
}
