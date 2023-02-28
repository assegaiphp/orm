<?php

namespace Tests\Unit;

use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\DataSource\DataSourceOptions;
use Assegai\Orm\DataSource\SQLCharacterSet;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\ORM\Exceptions\DataSourceException;
use Assegai\Orm\Management\DatabaseManager;
use Tests\Support\UnitTester;

class DatabaseManagerCest
{
  protected ?DataSourceOptions $dataSourceOptions = null;
  protected ?DataSource $dataSource = null;
  protected ?DatabaseManager $databaseManager = null;

  /**
   * @throws DataSourceException
   */
  public function _before(UnitTester $I): void
  {
    $config = require(__DIR__ . '/config/default.php');
    $databaseConfig = $config['databases']['mysql'];

    $this->dataSourceOptions = new DataSourceOptions(
      entities: [],
      database: $databaseConfig['name'] ?? '',
      type: DataSourceType::MARIADB,
      host: $databaseConfig['host'] ?? 'localhost',
      port: $databaseConfig['port'] ?? 3306,
      username: $databaseConfig['user'] ?? 'root',
      password: $databaseConfig['pass'] ?? '',
      charSet: $databaseConfig['charSet'] ?? SQLCharacterSet::UTF8MB4
    );
    $this->dataSource = new DataSource($this->dataSourceOptions);
    $this->databaseManager = DatabaseManager::getInstance();
  }

  // tests
  public function testTheExistsMethod(UnitTester $I): void
  {
    $expectedDatabaseName = "performance_schema";
    $nonExistentDatabaseTableName = "_1938";

    $I->assertTrue($this->databaseManager->exists($this->dataSource, $expectedDatabaseName));
    $I->assertFalse($this->databaseManager->exists($this->dataSource, $nonExistentDatabaseTableName));
  }

  /**
   * @throws DataSourceException
   */
  public function testTheCreateMethod(UnitTester $I): void
  {
    $expectedDatabaseName = "balloons_db";
    $nonExistentDatabaseTableName = "shields_db";
    $this->databaseManager->setup($this->dataSource, $expectedDatabaseName);

    $I->assertTrue($this->databaseManager->exists($this->dataSource, $expectedDatabaseName));
    $I->assertFalse($this->databaseManager->exists($this->dataSource, $nonExistentDatabaseTableName));
  }

  public function testTheDropMethod(UnitTester $I): void
  {
  }

  public function testTheResetMethod(UnitTester $I): void
  {
  }
}
