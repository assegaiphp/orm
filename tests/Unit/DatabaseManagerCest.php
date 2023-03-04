<?php

namespace Tests\Unit;

use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\DataSource\DataSourceOptions;
use Assegai\Orm\DataSource\SQLCharacterSet;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\ORM\Exceptions\DataSourceException;
use Assegai\Orm\Exceptions\NotImplementedException;
use Assegai\Orm\Management\DatabaseManager;
use PDOException;
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

  /**
   * @param UnitTester $I
   * @return void
   * @throws DataSourceException
   */
  public function testTheDropMethod(UnitTester $I): void
  {
    $dbName = 'zulu_db';
    $this->createDatabase($dbName);
    $this->createUsersTable($dbName);
    $I->amConnectedToDatabase($dbName);
    $I->haveInDatabase("$dbName.bantu", [
      'username' => 'kingshak1787',
      'email' => 'shaka.kaSenzangakhona@assegaiphp.com',
      'full_name' => 'Shaka Zulu'
    ]);

    $this->databaseManager->drop($this->dataSource, $dbName);
    $I->assertFalse($this->databaseManager->exists($this->dataSource, $dbName));
  }

  public function testTheResetMethod(UnitTester $I): void
  {
    throw new NotImplementedException(__METHOD__);
  }

  private function createDatabase(string $dbName): void
  {
    $sql = "CREATE DATABASE IF NOT EXISTS `$dbName` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
    $statement = $this->dataSource->db->query($sql);

    if (false === $statement->execute())
    {
      throw new PDOException("Failed to create database $dbName");
    }
  }

  /**
   * @param string $dbName
   * @return void
   */
  private function createUsersTable(string $dbName): void
  {
    $sql = <<<MYSQL
CREATE TABLE IF NOT EXISTS `$dbName`.`bantu` (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  full_name VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  deleted_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
MYSQL;
    $statement = $this->dataSource->db->query($sql);

    if (false === $statement->execute())
    {
      throw new PDOException("Failed to create users table in database $dbName");
    }
  }
}
