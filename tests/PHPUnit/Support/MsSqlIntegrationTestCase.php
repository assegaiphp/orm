<?php

namespace Tests\PHPUnit\Support;

use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\DataSource\DataSourceOptions;
use Assegai\Orm\DataSource\SchemaOptions;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Exceptions\DataSourceConnectionException;
use Assegai\Orm\Management\EntityManager;
use Assegai\Orm\Support\OrmRuntime;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Unit\mocks\AlteredMockEntity;
use Unit\mocks\MockEntity;

abstract class MsSqlIntegrationTestCase extends TestCase
{
    /** @var array{MSSQL_DB_HOST:string,MSSQL_DB_NAME:string,MSSQL_DB_PORT:string,MSSQL_DB_USER:string,MSSQL_DB_PASS:string} */
    protected array $config;
    protected DataSource $dataSource;
    protected EntityManager $manager;
    protected SchemaOptions $schemaOptions;
    protected string $databaseName;

    protected function setUp(): void
    {
        $config = $this->loadEnvironment();
        $this->config = $config;
        $this->databaseName = $config['MSSQL_DB_NAME'];
        $this->schemaOptions = new SchemaOptions(
            dbName: $this->databaseName,
            dialect: SQLDialect::MSSQL,
            checkIfExists: true,
        );
        OrmRuntime::mergeConfig([
            'databases' => [
                'mssql' => [
                    $this->databaseName => [
                        'host' => $config['MSSQL_DB_HOST'],
                        'port' => (int) $config['MSSQL_DB_PORT'],
                        'username' => $config['MSSQL_DB_USER'],
                        'password' => $config['MSSQL_DB_PASS'],
                        'database' => $this->databaseName,
                        'name' => $this->databaseName,
                    ],
                ],
            ],
        ]);

        try {
            $this->dataSource = new DataSource(new DataSourceOptions(
                entities: [MockEntity::class, AlteredMockEntity::class],
                name: $this->databaseName,
                type: DataSourceType::MSSQL,
                host: $config['MSSQL_DB_HOST'],
                port: (int) $config['MSSQL_DB_PORT'],
                username: $config['MSSQL_DB_USER'],
                password: $config['MSSQL_DB_PASS'],
            ));
        } catch (DataSourceConnectionException $exception) {
            throw new RuntimeException(
                sprintf(
                    'Unable to connect to the ORM MSSQL integration database using %s (host=%s port=%s db=%s user=%s).',
                    dirname(__DIR__, 3) . '/.env.mssql',
                    $config['MSSQL_DB_HOST'],
                    $config['MSSQL_DB_PORT'],
                    $config['MSSQL_DB_NAME'],
                    $config['MSSQL_DB_USER'],
                ),
                previous: $exception,
            );
        }

        $this->manager = $this->dataSource->manager;
        $this->resetMocksTable();
    }

    protected function tearDown(): void
    {
        if (isset($this->dataSource)) {
            $this->dropMocksTable();
            $this->dataSource->disconnect();
        }

        unset($this->manager, $this->dataSource, $this->databaseName, $this->config, $this->schemaOptions);
    }

    protected function resetMocksTable(): void
    {
        $this->dropMocksTable();
        $this->dataSource->getClient()->exec(
            'CREATE TABLE [mocks] (
                [id] INT IDENTITY(1,1) PRIMARY KEY,
                [name] NVARCHAR(255) NOT NULL UNIQUE,
                [description] NVARCHAR(MAX) NOT NULL,
                [color_type] NVARCHAR(32) NOT NULL,
                [created_at] DATETIME2 NULL,
                [updated_at] DATETIME2 NULL,
                [deleted_at] DATETIME2 NULL
            )'
        );
    }

    protected function dropMocksTable(): void
    {
        $this->dataSource->getClient()->exec("IF OBJECT_ID(N'[mocks]', N'U') IS NOT NULL DROP TABLE [mocks]");
    }

    protected function rowCount(string $tableName): int
    {
        $statement = $this->dataSource->getClient()->query(
            sprintf('SELECT COUNT(*) FROM [%s]', str_replace(']', ']]', $tableName))
        );

        return (int) $statement->fetchColumn();
    }

    protected function fetchMocksRowById(int $id): array
    {
        $statement = $this->dataSource->getClient()->prepare(
            'SELECT [id], [name], [description], [color_type] FROM [mocks] WHERE [id] = :id'
        );
        $statement->execute(['id' => $id]);

        $row = $statement->fetch();

        if (!is_array($row)) {
            throw new RuntimeException("Expected to find mocks row for id {$id}");
        }

        return $row;
    }

    protected function tempDatabaseName(string $suffix): string
    {
        return 'assegai_' . $suffix . '_' . substr(str_replace('.', '', uniqid('', true)), -12);
    }

    /**
     * @return array{MSSQL_DB_HOST:string,MSSQL_DB_NAME:string,MSSQL_DB_PORT:string,MSSQL_DB_USER:string,MSSQL_DB_PASS:string}
     */
    private function loadEnvironment(): array
    {
        if (!extension_loaded('pdo_sqlsrv') || !in_array('sqlsrv', PDO::getAvailableDrivers(), true)) {
            self::markTestSkipped('MSSQL integration environment not configured. Install or enable ext-pdo_sqlsrv.');
        }

        $env = [];
        $envFile = dirname(__DIR__, 3) . '/.env.mssql';

        if (is_file($envFile)) {
            $env = parse_ini_file($envFile, false, INI_SCANNER_RAW) ?: [];
        } else {
            foreach (['MSSQL_DB_HOST', 'MSSQL_DB_NAME', 'MSSQL_DB_PORT', 'MSSQL_DB_USER', 'MSSQL_DB_PASS'] as $key) {
                $value = getenv($key);

                if ($value !== false) {
                    $env[$key] = $value;
                }
            }
        }

        foreach (['MSSQL_DB_HOST', 'MSSQL_DB_NAME', 'MSSQL_DB_PORT', 'MSSQL_DB_USER', 'MSSQL_DB_PASS'] as $requiredKey) {
            if (!array_key_exists($requiredKey, $env) || $env[$requiredKey] === '') {
                self::markTestSkipped(
                    sprintf(
                        'MSSQL integration environment not configured. Create %s or set %s.',
                        $envFile,
                        $requiredKey,
                    )
                );
            }
        }

        return $env;
    }
}
