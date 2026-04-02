<?php

namespace Tests\PHPUnit\Unit;

use Assegai\Orm\DataSource\DBFactory;
use Assegai\Orm\DataSource\DataSourceOptions;
use Assegai\Orm\DataSource\SQLCharacterSet;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Enumerations\SQLDialect;
use PDO;
use PHPUnit\Framework\TestCase;

final class ConnectionConfigTest extends TestCase
{
    public function testIncludesConfiguredCharsetInMySqlDsn(): void
    {
        $dsn = DBFactory::buildMySqlDsn('localhost', 3306, 'assegai', SQLCharacterSet::UTF8MB4);

        self::assertSame('mysql:host=localhost;port=3306;dbname=assegai;charset=utf8mb4', $dsn);
    }

    public function testAppliesSafeDefaultMysqlPdoAttributes(): void
    {
        $attributes = DBFactory::getDefaultPdoAttributes(SQLDialect::MYSQL);

        self::assertSame(PDO::ERRMODE_EXCEPTION, $attributes[PDO::ATTR_ERRMODE]);
        self::assertSame(PDO::FETCH_ASSOC, $attributes[PDO::ATTR_DEFAULT_FETCH_MODE]);
        self::assertFalse($attributes[PDO::ATTR_STRINGIFY_FETCHES]);
        self::assertFalse($attributes[PDO::ATTR_EMULATE_PREPARES]);
    }

    public function testReadsCharSetFromOptionsArrays(): void
    {
        $options = DataSourceOptions::fromArray([
            'database' => 'assegai',
            'type' => DataSourceType::MYSQL,
            'charset' => 'utf8mb4',
        ]);

        self::assertSame(SQLCharacterSet::UTF8MB4, $options->charSet);
    }

    public function testDoesNotCacheSqliteConnectionsAcrossFactoryCalls(): void
    {
        $path = sys_get_temp_dir() . '/assegai-sqlite-factory-' . uniqid('', true) . '.sqlite';
        @unlink($path);

        $first = DBFactory::getSQLiteConnection($path);
        $second = DBFactory::getSQLiteConnection($path);

        self::assertNotSame($first, $second);

        $first = null;
        $second = null;
        @unlink($path);
    }
}
