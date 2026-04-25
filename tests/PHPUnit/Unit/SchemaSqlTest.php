<?php

namespace Tests\PHPUnit\Unit;

use Assegai\Orm\DataSource\Schema;
use Assegai\Orm\DataSource\SchemaOptions;
use Assegai\Orm\DataSource\SQLCharacterSet;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Enumerations\SchemaEngineType;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Unit\mocks\MockEntity;
use Unit\mocks\MsSqlSchemaEntity;
use Unit\mocks\MySqlEngineEntity;
use Unit\mocks\PostgreSqlSchemaEntity;
use Unit\mocks\SqliteWithoutRowIdEntity;

require_once __DIR__ . '/../../Unit/mocks/MsSqlSchemaEntity.php';
require_once __DIR__ . '/../../Unit/mocks/MySqlEngineEntity.php';
require_once __DIR__ . '/../../Unit/mocks/PostgreSqlSchemaEntity.php';
require_once __DIR__ . '/../../Unit/mocks/SqliteWithoutRowIdEntity.php';

final class SchemaSqlTest extends TestCase
{
    public function testCreateIfNotExistsDdlIncludesGuardAndConfiguredMysqlOptions(): void
    {
        $method = new ReflectionMethod(Schema::class, 'getDDLStatementFromEntity');

        $sql = $method->invoke(
            null,
            MockEntity::class,
            new SchemaOptions(
                dbName: 'assegai_test_db',
                dialect: SQLDialect::MYSQL,
                checkIfExists: true,
                characterSet: SQLCharacterSet::LATIN1,
                engine: SchemaEngineType::MY_ISAM,
            )
        );

        self::assertStringContainsString('CREATE TABLE IF NOT EXISTS `assegai_test_db`.`mocks`', $sql);
        self::assertStringContainsString('ENGINE=MyISAM', $sql);
        self::assertStringContainsString('DEFAULT CHARSET=latin1', $sql);
        self::assertStringContainsString('COLLATE=latin1_swedish_ci', $sql);
    }

    public function testCreateIfNotExistsDdlUsesSqlServerGuardSyntaxForMsSql(): void
    {
        $method = new ReflectionMethod(Schema::class, 'getDDLStatementFromEntity');

        $sql = $method->invoke(
            null,
            MockEntity::class,
            new SchemaOptions(
                dbName: 'assegai_test_db',
                dialect: SQLDialect::MSSQL,
                checkIfExists: true,
            )
        );

        self::assertStringContainsString(
            "IF OBJECT_ID(N'[mocks]', N'U') IS NULL CREATE TABLE [mocks]",
            $sql
        );
        self::assertStringContainsString('[id] BIGINT IDENTITY(1,1) PRIMARY KEY', $sql);
        self::assertStringContainsString('[name] VARCHAR(255) NOT NULL', $sql);
        self::assertStringContainsString("[color_type] NVARCHAR(MAX) DEFAULT 'RED'", $sql);
    }


    public function testCreateDdlUsesEntityEngineWhenSchemaOptionsDoNotOverrideIt(): void
    {
        $method = new ReflectionMethod(Schema::class, 'getDDLStatementFromEntity');

        $sql = $method->invoke(
            null,
            MySqlEngineEntity::class,
            new SchemaOptions(
                dbName: 'assegai_test_db',
                dialect: SQLDialect::MYSQL,
                checkIfExists: true,
            )
        );

        self::assertStringContainsString('ENGINE=MyISAM', $sql);
    }

    public function testCreateDdlStillSupportsLegacyEntityEngineFallback(): void
    {
        $method = new ReflectionMethod(Schema::class, 'getDDLStatementFromEntity');

        $entityClass = new #[\Assegai\Orm\Attributes\Entity(
            table: 'legacy_engine_mocks',
            dataSource: 'assegai_test_db',
            engine: 'MyISAM',
        )] class () {
            #[\Assegai\Orm\Attributes\Columns\PrimaryGeneratedColumn]
            public ?int $id = null;
        };

        $sql = $method->invoke(
            null,
            $entityClass::class,
            new SchemaOptions(
                dbName: 'assegai_test_db',
                dialect: SQLDialect::MYSQL,
                checkIfExists: true,
            )
        );

        self::assertStringContainsString('ENGINE=MyISAM', $sql);
    }

    public function testCreateDdlAppendsWithoutRowIdForSqliteEntitiesThatOptOut(): void
    {
        $method = new ReflectionMethod(Schema::class, 'getDDLStatementFromEntity');

        $sql = $method->invoke(
            null,
            SqliteWithoutRowIdEntity::class,
            new SchemaOptions(
                dbName: 'sqlite_test_db',
                dialect: SQLDialect::SQLITE,
                checkIfExists: true,
            )
        );

        self::assertStringContainsString('CREATE TABLE IF NOT EXISTS "sqlite_without_rowid"', $sql);
        self::assertStringEndsWith('WITHOUT ROWID', $sql);
    }

    public function testCreateDdlQualifiesPostgreSqlSchemaWhenEntityDeclaresOne(): void
    {
        $method = new ReflectionMethod(Schema::class, 'getDDLStatementFromEntity');

        $sql = $method->invoke(
            null,
            PostgreSqlSchemaEntity::class,
            new SchemaOptions(
                dbName: 'assegai_test_db',
                dialect: SQLDialect::POSTGRESQL,
                checkIfExists: true,
            )
        );

        self::assertStringContainsString('CREATE TABLE IF NOT EXISTS "reporting"."pg_schema_mocks"', $sql);
    }

    public function testCreateDdlStillSupportsLegacyEntitySchemaFallback(): void
    {
        $method = new ReflectionMethod(Schema::class, 'getDDLStatementFromEntity');

        $entityClass = new #[\Assegai\Orm\Attributes\Entity(
            table: 'legacy_pg_schema_mocks',
            dataSource: 'assegai_test_db',
            driver: DataSourceType::POSTGRESQL,
            schema: 'reporting',
        )] class () {
            #[\Assegai\Orm\Attributes\Columns\PrimaryGeneratedColumn]
            public ?int $id = null;
        };

        $sql = $method->invoke(
            null,
            $entityClass::class,
            new SchemaOptions(
                dbName: 'assegai_test_db',
                dialect: SQLDialect::POSTGRESQL,
                checkIfExists: true,
            )
        );

        self::assertStringContainsString('CREATE TABLE IF NOT EXISTS "reporting"."legacy_pg_schema_mocks"', $sql);
    }

    public function testCreateDdlQualifiesMsSqlSchemaWhenEntityDeclaresOne(): void
    {
        $method = new ReflectionMethod(Schema::class, 'getDDLStatementFromEntity');

        $sql = $method->invoke(
            null,
            MsSqlSchemaEntity::class,
            new SchemaOptions(
                dbName: 'assegai_test_db',
                dialect: SQLDialect::MSSQL,
                checkIfExists: true,
            )
        );

        self::assertStringContainsString(
            "IF OBJECT_ID(N'[reporting].[mssql_schema_mocks]', N'U') IS NULL CREATE TABLE [reporting].[mssql_schema_mocks]",
            $sql
        );
    }

    public function testMsSqlRenameSourceRespectsConfiguredSchema(): void
    {
        $method = new ReflectionMethod(Schema::class, 'getMsSqlRenameSource');

        $sql = $method->invoke(
            null,
            'mssql_schema_mocks',
            new SchemaOptions(
                dbName: 'assegai_test_db',
                dialect: SQLDialect::MSSQL,
                schema: 'reporting',
            )
        );

        self::assertSame('[reporting].[mssql_schema_mocks]', $sql);
    }

    public function testMsSqlRenameSourcePreservesExplicitQualifiedNames(): void
    {
        $method = new ReflectionMethod(Schema::class, 'getMsSqlRenameSource');

        $sql = $method->invoke(
            null,
            'archive.mssql_schema_mocks',
            new SchemaOptions(
                dbName: 'assegai_test_db',
                dialect: SQLDialect::MSSQL,
                schema: 'reporting',
            )
        );

        self::assertSame('[archive].[mssql_schema_mocks]', $sql);
    }

    public function testMsSqlRenameTargetUsesOnlyTheObjectName(): void
    {
        $method = new ReflectionMethod(Schema::class, 'getMsSqlRenameTarget');

        self::assertSame('renamed_mocks', $method->invoke(null, 'reporting.renamed_mocks'));
    }

    public function testCreateDdlStillSupportsLegacyEntityWithoutRowIdFallback(): void
    {
        $method = new ReflectionMethod(Schema::class, 'getDDLStatementFromEntity');

        $entityClass = new #[\Assegai\Orm\Attributes\Entity(
            table: 'legacy_sqlite_without_rowid',
            dataSource: 'sqlite_test_db',
            driver: DataSourceType::SQLITE,
            withRowId: false,
        )] class () {
            #[\Assegai\Orm\Attributes\Columns\PrimaryGeneratedColumn]
            public ?int $id = null;
        };

        $sql = $method->invoke(
            null,
            $entityClass::class,
            new SchemaOptions(
                dbName: 'sqlite_test_db',
                dialect: SQLDialect::SQLITE,
                checkIfExists: true,
            )
        );

        self::assertStringContainsString('CREATE TABLE IF NOT EXISTS "legacy_sqlite_without_rowid"', $sql);
        self::assertStringEndsWith('WITHOUT ROWID', $sql);
    }
}
