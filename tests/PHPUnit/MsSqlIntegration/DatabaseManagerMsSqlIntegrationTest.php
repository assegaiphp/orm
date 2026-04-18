<?php

namespace Tests\PHPUnit\MsSqlIntegration;

use Assegai\Orm\Management\DatabaseManager;
use Tests\PHPUnit\Support\MsSqlIntegrationTestCase;

final class DatabaseManagerMsSqlIntegrationTest extends MsSqlIntegrationTestCase
{
    private DatabaseManager $databaseManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->databaseManager = DatabaseManager::getInstance();
    }

    public function testExistsRecognizesCurrentAndMissingDatabases(): void
    {
        self::assertTrue($this->databaseManager->exists($this->dataSource, $this->databaseName));
        self::assertFalse($this->databaseManager->exists($this->dataSource, $this->tempDatabaseName('missing')));
    }
}
