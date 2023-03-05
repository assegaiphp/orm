<?php


namespace Tests\Unit;

use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\ORM\Exceptions\DataSourceException;
use Assegai\Orm\Exceptions\IOException;
use Assegai\Orm\Exceptions\MigrationException;
use Assegai\Orm\Exceptions\NotImplementedException;
use Assegai\Orm\Migrations\Migration;
use Assegai\Orm\Migrations\Migrator;
use Tests\Support\UnitTester;

class MigratorCest
{
  protected ?Migrator $migrator = null;
  protected ?Migrator $nonExistentDBMigrator = null;

  const MIGRATIONS_DIR = __DIR__ . DIRECTORY_SEPARATOR . 'database/migrations';

  /**
   * @param UnitTester $I
   * @return void
   * @throws DataSourceException
   * @throws IOException
   */
  public function _before(UnitTester $I): void
  {
    $config = require(__DIR__ . '/config/default.php');

    if ($dbConfig = $config['databases']['mysql'])
    {
      $datasource = new DataSource([
        'database' => $dbConfig['name'],
        'type' => DataSourceType::MARIADB,
        'host' => $dbConfig['host'],
        'port' => 3306,
        'username' => $dbConfig['user'],
        'password' => $dbConfig['pass'],
      ]);

      $this->migrator = new Migrator($datasource, self::MIGRATIONS_DIR);
    }
  }

  public function testTheGeneratemigrationfilenameMethod(UnitTester $I): void
  {
    $filename = 'create test table';
    $expectedName = $this->migrator->generateMigrationNamePrefix() . 'create_test_table.php';
    $text = "generating the migration name " . $expectedName;
    $I->wantToTest($text);

    $I->assertEquals($expectedName, $this->migrator->generateMigrationFileName($filename));
  }

  // tests

  /**
   * @param UnitTester $I
   * @return void
   * @throws MigrationException
   */
  public function testTheGenerateMethod(UnitTester $I): void
  {
    $I->wantToTest("generating a migration file from a snake-case name");
    $migrationName = "create_test_table";
    $filename =
      self::MIGRATIONS_DIR . DIRECTORY_SEPARATOR . $this->migrator->generateMigrationFileName('create_test_table');

    $this->migrator->generate($migrationName, getcwd() . '/tests/Unit');
    $I->assertFileExists($filename);

    # Test invalid file name
    $invalidFileName = 1;

    # Delete generated files
  }

  public function testTheRunMethod(): void
  {
    throw new NotImplementedException(__METHOD__);
  }

  public function testTheRedoMethod(): void
  {
    throw new NotImplementedException(__METHOD__);
  }

  public function testTheRevertMethod(): void
  {
    throw new NotImplementedException(__METHOD__);
  }
}
