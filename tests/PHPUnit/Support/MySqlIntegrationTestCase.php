<?php

namespace Tests\PHPUnit\Support;

use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\DataSource\DataSourceOptions;
use Assegai\Orm\DataSource\SQLCharacterSet;
use Assegai\Orm\DataSource\Schema;
use Assegai\Orm\DataSource\SchemaOptions;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Exceptions\DataSourceConnectionException;
use Assegai\Orm\Management\EntityManager;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Unit\mocks\AlteredMockEntity;
use Unit\mocks\MockEntity;

abstract class MySqlIntegrationTestCase extends TestCase
{
    protected DataSource $dataSource;
    protected EntityManager $manager;
    protected SchemaOptions $schemaOptions;
    protected string $databaseName;

    protected function setUp(): void
    {
        $config = $this->loadEnvironment();

        $this->databaseName = $config['DB_NAME'];
        $this->schemaOptions = new SchemaOptions(
            dbName: $this->databaseName,
            dialect: SQLDialect::MYSQL,
            checkIfExists: true,
        );

        try {
            $this->dataSource = new DataSource(new DataSourceOptions(
                entities: [MockEntity::class, AlteredMockEntity::class],
                name: $this->databaseName,
                type: DataSourceType::MYSQL,
                host: $config['DB_HOST'],
                port: (int) $config['DB_PORT'],
                username: $config['DB_USER'],
                password: $config['DB_PASS'],
                charSet: SQLCharacterSet::UTF8MB4,
            ));
        } catch (DataSourceConnectionException $exception) {
            throw new RuntimeException(
                sprintf(
                    'Unable to connect to the ORM MySQL integration database using %s (host=%s port=%s db=%s user=%s).',
                    dirname(__DIR__, 3) . '/.env',
                    $config['DB_HOST'],
                    $config['DB_PORT'],
                    $config['DB_NAME'],
                    $config['DB_USER'],
                ),
                previous: $exception,
            );
        }

        $this->manager = $this->dataSource->manager;
        $this->resetMocksTable();
    }

    protected function tearDown(): void
    {
        if (isset($this->dataSource, $this->schemaOptions)) {
            Schema::dropIfExists(MockEntity::class, $this->schemaOptions);
        }

        unset($this->manager, $this->dataSource, $this->schemaOptions);
    }

    protected function resetMocksTable(): void
    {
        Schema::dropIfExists(MockEntity::class, $this->schemaOptions);
        self::assertTrue(Schema::createIfNotExists(MockEntity::class, $this->schemaOptions));
    }

    protected function rowCount(string $tableName): int
    {
        return (int) $this->dataSource->getClient()->query("SELECT COUNT(*) FROM `$tableName`")->fetchColumn();
    }

    protected function fetchMocksRowById(int $id): array
    {
        $statement = $this->dataSource->getClient()->prepare(
            'SELECT `id`, `name`, `description`, `color_type` FROM `mocks` WHERE `id` = :id'
        );
        $statement->execute(['id' => $id]);

        $row = $statement->fetch();

        if (!is_array($row)) {
            throw new RuntimeException("Expected to find mocks row for id {$id}");
        }

        return $row;
    }

    /**
     * @return array{DB_HOST:string,DB_NAME:string,DB_PORT:string,DB_USER:string,DB_PASS:string}
     */
    private function loadEnvironment(): array
    {
        $envFile = dirname(__DIR__, 3) . '/.env';
        $env = parse_ini_file($envFile, false, INI_SCANNER_RAW);

        if (!is_array($env)) {
            throw new RuntimeException("Unable to load ORM test environment from {$envFile}");
        }

        foreach (['DB_HOST', 'DB_NAME', 'DB_PORT', 'DB_USER', 'DB_PASS'] as $requiredKey) {
            if (!array_key_exists($requiredKey, $env)) {
                throw new RuntimeException("Missing {$requiredKey} in {$envFile}");
            }
        }

        return $env;
    }
}
