<?php

namespace Tests\PHPUnit\Unit;

use Assegai\Orm\DataSource\DBFactory;
use Assegai\Orm\DataSource\DataSource;
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

    public function testCachesSqliteConnectionsAcrossFactoryCalls(): void
    {
        $path = sys_get_temp_dir() . '/assegai-sqlite-factory-' . uniqid('', true) . '.sqlite';
        @unlink($path);

        $first = DBFactory::getSQLiteConnection($path);
        $second = DBFactory::getSQLiteConnection($path);

        self::assertSame($first, $second);

        DBFactory::disconnectConnection($path, SQLDialect::SQLITE);
        $first = null;
        $second = null;
        @unlink($path);
    }

    public function testAppliesSqliteBusyTimeoutPragma(): void
    {
        $connection = DBFactory::getSQLiteConnection(':memory:');
        $busyTimeout = (int) $connection->query('PRAGMA busy_timeout')->fetchColumn();

        self::assertSame(5000, $busyTimeout);

        DBFactory::disconnectConnection(':memory:', SQLDialect::SQLITE);
    }

    public function testEnablesWalModeForFileBackedSqliteConnections(): void
    {
        $path = sys_get_temp_dir() . '/assegai-sqlite-wal-' . uniqid('', true) . '.sqlite';
        self::cleanupSqliteFiles($path);

        $connection = DBFactory::getSQLiteConnection($path);
        $journalMode = strtolower((string) $connection->query('PRAGMA journal_mode')->fetchColumn());

        self::assertSame('wal', $journalMode);

        DBFactory::disconnectConnection($path, SQLDialect::SQLITE);
        self::cleanupSqliteFiles($path);
    }

    public function testFileBackedSqliteDataSourcesReuseManagedConnections(): void
    {
        $path = sys_get_temp_dir() . '/assegai-sqlite-datasource-' . uniqid('', true) . '.sqlite';
        @unlink($path);

        $first = new DataSource(new DataSourceOptions(entities: [], name: $path, type: DataSourceType::SQLITE));
        $second = new DataSource(new DataSourceOptions(entities: [], name: $path, type: DataSourceType::SQLITE));

        self::assertSame($first->getClient(), $second->getClient());

        $first->disconnect();
        $second->disconnect();
        @unlink($path);
    }

    public function testDisconnectRollsBackActiveSqliteTransactions(): void
    {
        $path = sys_get_temp_dir() . '/assegai-sqlite-transaction-' . uniqid('', true) . '.sqlite';
        @unlink($path);

        $dataSource = new DataSource(new DataSourceOptions(entities: [], name: $path, type: DataSourceType::SQLITE));
        $dataSource->getClient()->exec('CREATE TABLE tx_probe (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL)');
        $dataSource->getClient()->beginTransaction();
        $dataSource->getClient()->exec("INSERT INTO tx_probe (name) VALUES ('pending')");

        $dataSource->disconnect();

        $freshDataSource = new DataSource(new DataSourceOptions(entities: [], name: $path, type: DataSourceType::SQLITE));
        $rowCount = (int) $freshDataSource->getClient()->query('SELECT COUNT(*) FROM tx_probe')->fetchColumn();

        self::assertSame(0, $rowCount);

        $freshDataSource->disconnect();
        self::cleanupSqliteFiles($path);
    }

    private static function cleanupSqliteFiles(string $path): void
    {
        @unlink($path);
        @unlink($path . '-wal');
        @unlink($path . '-shm');
    }
}
