<?php

namespace Tests\PHPUnit\Unit;

use Assegai\Orm\DataSource\Schema;
use Assegai\Orm\DataSource\SchemaOptions;
use Assegai\Orm\DataSource\SQLCharacterSet;
use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Enumerations\SchemaEngineType;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Unit\mocks\MockEntity;

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
}
