<?php

namespace Tests\PHPUnit\PostgreSQLIntegration;

use Assegai\Orm\Management\DatabaseManager;
use Tests\PHPUnit\Support\PostgreSqlIntegrationTestCase;
use Throwable;

final class DatabaseManagerPostgreSqlIntegrationTest extends PostgreSqlIntegrationTestCase
{
    private DatabaseManager $databaseManager;

    /** @var string[] */
    private array $createdDatabaseNames = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->databaseManager = DatabaseManager::getInstance();
    }

    protected function tearDown(): void
    {
        foreach (array_reverse($this->createdDatabaseNames) as $databaseName) {
            try {
                if ($this->databaseManager->exists($this->dataSource, $databaseName, false)) {
                    $this->databaseManager->drop($this->dataSource, $databaseName);
                }
            } catch (Throwable) {
            }
        }

        parent::tearDown();
    }

    public function testExistsRecognizesCurrentAndMissingDatabases(): void
    {
        self::assertTrue($this->databaseManager->exists($this->dataSource, $this->databaseName));
        self::assertFalse($this->databaseManager->exists($this->dataSource, $this->tempDatabaseName('missing')));
    }

    public function testSetupCreatesDatabaseWhenMissing(): void
    {
        $databaseName = $this->tempDatabaseName('setup');

        self::assertFalse($this->databaseManager->exists($this->dataSource, $databaseName));

        $this->databaseManager->setup($this->dataSource, $databaseName);
        $this->createdDatabaseNames[] = $databaseName;

        self::assertTrue($this->databaseManager->exists($this->dataSource, $databaseName));
    }

    public function testDropRemovesDatabase(): void
    {
        $databaseName = $this->tempDatabaseName('drop');

        $this->databaseManager->setup($this->dataSource, $databaseName);
        self::assertTrue($this->databaseManager->exists($this->dataSource, $databaseName));

        $this->databaseManager->drop($this->dataSource, $databaseName);

        self::assertFalse($this->databaseManager->exists($this->dataSource, $databaseName));
    }

    public function testResetRecreatesDatabaseWithoutKeepingOldTables(): void
    {
        $databaseName = $this->tempDatabaseName('reset');
        $tableName = 'transient_records';

        $this->databaseManager->setup($this->dataSource, $databaseName);
        $this->createdDatabaseNames[] = $databaseName;

        $temporaryDataSource = $this->createPostgreSqlDataSource($databaseName);
        try {
            $temporaryDataSource->getClient()->exec(
                'CREATE TABLE IF NOT EXISTS "transient_records" ("id" BIGSERIAL PRIMARY KEY)'
            );
        } finally {
            $temporaryDataSource->disconnect();
        }

        self::assertTrue($this->hasTable($databaseName, $tableName));

        $this->databaseManager->reset($this->dataSource, $databaseName);

        self::assertTrue($this->databaseManager->exists($this->dataSource, $databaseName));
        self::assertFalse($this->hasTable($databaseName, $tableName));
    }

    private function hasTable(string $databaseName, string $tableName): bool
    {
        $temporaryDataSource = $this->createPostgreSqlDataSource($databaseName);

        try {
            $statement = $temporaryDataSource->getClient()->prepare(
                'SELECT table_name FROM information_schema.tables WHERE table_schema = current_schema() AND table_name = :table LIMIT 1'
            );
            $statement->execute(['table' => $tableName]);

            return $statement->fetchColumn() !== false;
        } finally {
            $temporaryDataSource->disconnect();
        }
    }
}
