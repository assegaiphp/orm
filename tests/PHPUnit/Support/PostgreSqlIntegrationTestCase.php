<?php

namespace Tests\PHPUnit\Support;

use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\DataSource\DataSourceOptions;
use Assegai\Orm\DataSource\SchemaOptions;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Exceptions\DataSourceConnectionException;
use Assegai\Orm\Management\EntityManager;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Unit\mocks\AlteredMockEntity;
use Unit\mocks\MockEntity;

abstract class PostgreSqlIntegrationTestCase extends TestCase
{
    /** @var array{PG_DB_HOST:string,PG_DB_NAME:string,PG_DB_PORT:string,PG_DB_USER:string,PG_DB_PASS:string} */
    protected array $config;
    protected DataSource $dataSource;
    protected EntityManager $manager;
    protected SchemaOptions $schemaOptions;
    protected string $databaseName;

    protected function setUp(): void
    {
        $config = $this->loadEnvironment();
        $this->config = $config;
        $this->databaseName = $config['PG_DB_NAME'];
        $this->schemaOptions = new SchemaOptions(
            dbName: $this->databaseName,
            dialect: SQLDialect::POSTGRESQL,
            checkIfExists: true,
        );

        try {
            $this->dataSource = new DataSource(new DataSourceOptions(
                entities: [MockEntity::class, AlteredMockEntity::class],
                name: $this->databaseName,
                type: DataSourceType::POSTGRESQL,
                host: $config['PG_DB_HOST'],
                port: (int) $config['PG_DB_PORT'],
                username: $config['PG_DB_USER'],
                password: $config['PG_DB_PASS'],
            ));
        } catch (DataSourceConnectionException $exception) {
            throw new RuntimeException(
                sprintf(
                    'Unable to connect to the ORM PostgreSQL integration database using %s (host=%s port=%s db=%s user=%s).',
                    dirname(__DIR__, 3) . '/.env.pgsql',
                    $config['PG_DB_HOST'],
                    $config['PG_DB_PORT'],
                    $config['PG_DB_NAME'],
                    $config['PG_DB_USER'],
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
            $this->dataSource->getClient()->exec('DROP TABLE IF EXISTS "mocks"');
            $this->dataSource->disconnect();
        }

        unset($this->manager, $this->dataSource, $this->schemaOptions, $this->databaseName, $this->config);
    }

    protected function resetMocksTable(): void
    {
        $this->dataSource->getClient()->exec('DROP TABLE IF EXISTS "mocks"');
        $this->dataSource->getClient()->exec(
            'CREATE TABLE "mocks" (
                "id" BIGSERIAL PRIMARY KEY,
                "name" VARCHAR(255) NOT NULL UNIQUE,
                "description" TEXT NOT NULL,
                "color_type" VARCHAR(32) NOT NULL,
                "created_at" TIMESTAMP NULL,
                "updated_at" TIMESTAMP NULL,
                "deleted_at" TIMESTAMP NULL
            )'
        );
    }

    protected function rowCount(string $tableName): int
    {
        $statement = $this->dataSource->getClient()->query(
            sprintf('SELECT COUNT(*) FROM "%s"', str_replace('"', '""', $tableName))
        );

        return (int) $statement->fetchColumn();
    }

    protected function fetchMocksRowById(int $id): array
    {
        $statement = $this->dataSource->getClient()->prepare(
            'SELECT "id", "name", "description", "color_type" FROM "mocks" WHERE "id" = :id'
        );
        $statement->execute(['id' => $id]);

        $row = $statement->fetch();

        if (!is_array($row)) {
            throw new RuntimeException("Expected to find mocks row for id {$id}");
        }

        return $row;
    }

    protected function createPostgreSqlDataSource(string $databaseName, array $entities = []): DataSource
    {
        return new DataSource(new DataSourceOptions(
            entities: $entities,
            name: $databaseName,
            type: DataSourceType::POSTGRESQL,
            host: $this->config['PG_DB_HOST'],
            port: (int) $this->config['PG_DB_PORT'],
            username: $this->config['PG_DB_USER'],
            password: $this->config['PG_DB_PASS'],
        ));
    }

    protected function tempDatabaseName(string $suffix): string
    {
        return 'assegai_' . $suffix . '_' . substr(str_replace('.', '', uniqid('', true)), -12);
    }

    /**
     * @return array{PG_DB_HOST:string,PG_DB_NAME:string,PG_DB_PORT:string,PG_DB_USER:string,PG_DB_PASS:string}
     */
    private function loadEnvironment(): array
    {
        $env = [];
        $envFile = dirname(__DIR__, 3) . '/.env.pgsql';

        if (is_file($envFile)) {
            $env = parse_ini_file($envFile, false, INI_SCANNER_RAW) ?: [];
        } else {
            foreach (['PG_DB_HOST', 'PG_DB_NAME', 'PG_DB_PORT', 'PG_DB_USER', 'PG_DB_PASS'] as $key) {
                $value = getenv($key);

                if ($value !== false) {
                    $env[$key] = $value;
                }
            }
        }

        foreach (['PG_DB_HOST', 'PG_DB_NAME', 'PG_DB_PORT', 'PG_DB_USER', 'PG_DB_PASS'] as $requiredKey) {
            if (!array_key_exists($requiredKey, $env) || $env[$requiredKey] === '') {
                self::markTestSkipped(
                    sprintf(
                        'PostgreSQL integration environment not configured. Create %s or set %s.',
                        $envFile,
                        $requiredKey,
                    )
                );
            }
        }

        return $env;
    }
}
