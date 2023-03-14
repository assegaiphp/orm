<?php


namespace Tests\Unit;

use Assegai\Core\Util\Paths;
use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\ORM\Exceptions\DataSourceException;
use Assegai\Orm\Exceptions\IOException;
use Assegai\Orm\Exceptions\MigrationException;
use Assegai\Orm\Exceptions\NotImplementedException;
use Assegai\Orm\Migrations\Migration;
use Assegai\Orm\Migrations\Migrator;
use Codeception\Attribute\Incomplete;
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
    $configFilename = Paths::join(__DIR__, 'config', 'default.php');
    $config = require($configFilename);

    if (is_array($config))
    {
      $allMySQLDatabases = $config['databases']['mysql'];
      foreach ($allMySQLDatabases as $databaseName => $dbConfig)
      {
        $datasource = new DataSource([
          'database' => $databaseName,
          'type' => $dbConfig['type'] ?? DataSourceType::MARIADB,
          'host' => $dbConfig['host'] ?? 'localhost',
          'port' => $dbConfig['port'] ?? 3306,
          'username' => $dbConfig['user'] ?? '',
          'password' => $dbConfig['pass'] ?? '',
        ]);

        $this->migrator = new Migrator($datasource, self::MIGRATIONS_DIR);
      }
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
      Paths::join(self::MIGRATIONS_DIR, $this->migrator->generateMigrationFileName('create_test_table'));

    $this->migrator->generate($migrationName, getcwd() . '/tests/Unit');
    $I->assertFileExists($filename);

    # Test invalid file name
    $invalidFileName = 1;

    # Delete generated files
  }

  #[Incomplete]
  public function testTheRunMethod(): void
  {
  }

  #[Incomplete]
  public function testTheRedoMethod(): void
  {
  }

  #[Incomplete]
  public function testTheRevertMethod(): void
  {
  }
}
