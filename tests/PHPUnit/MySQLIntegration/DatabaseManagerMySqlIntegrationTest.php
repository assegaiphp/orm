<?php

namespace Tests\PHPUnit\MySQLIntegration;

use Assegai\Orm\Management\DatabaseManager;
use Tests\PHPUnit\Support\MySqlIntegrationTestCase;
use Throwable;

final class DatabaseManagerMySqlIntegrationTest extends MySqlIntegrationTestCase
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

        $this->dataSource->getClient()->exec(
            "CREATE TABLE IF NOT EXISTS `{$databaseName}`.`{$tableName}` (`id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY)"
        );

        self::assertTrue($this->hasTable($databaseName, $tableName));

        $this->databaseManager->reset($this->dataSource, $databaseName);

        self::assertTrue($this->databaseManager->exists($this->dataSource, $databaseName));
        self::assertFalse($this->hasTable($databaseName, $tableName));
    }

    private function hasTable(string $databaseName, string $tableName): bool
    {
        $statement = $this->dataSource->getClient()->prepare(
            'SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = :database AND TABLE_NAME = :table LIMIT 1'
        );
        $statement->execute([
            'database' => $databaseName,
            'table' => $tableName,
        ]);

        return $statement->fetchColumn() !== false;
    }

    private function tempDatabaseName(string $suffix): string
    {
        return 'assegai_' . $suffix . '_' . substr(str_replace('.', '', uniqid('', true)), -12);
    }
}
