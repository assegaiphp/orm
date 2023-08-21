<?php


namespace Tests\Unit;

use Assegai\Core\Util\Paths;
use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\ORM\Exceptions\DataSourceException;
use Assegai\Orm\Exceptions\IOException;
use Assegai\Orm\Exceptions\MigrationException;
use Assegai\Orm\Exceptions\ORMException;
use Assegai\Orm\Migrations\Migrator;
use Codeception\Attribute\Skip;
use Codeception\Scenario;
use Tests\Support\UnitTester;

class MigratorCest
{
  protected ?Migrator $migrator = null;
  protected ?Migrator $nonExistentDBMigrator = null;
  protected string $workingDirectory = '';
  protected string $staticMigrationsDirectory = '';
  protected ?DataSource $dataSource = null;
  protected string $databaseName = '';

  /** @var string[] $migrationNames  */
  protected array $migrationNames = [
    'create_users',
    'create_posts',
    'create_categories'
  ];

  protected const MIGRATIONS_DIR = __DIR__ . DIRECTORY_SEPARATOR . 'database/migrations';
  protected const MIGRATIONS_SCHEMA_TABLE_NAME = '__assegai_schema_migrations';

  /**
   * @param UnitTester $I
   * @param Scenario $scenario
   * @return void
   * @throws DataSourceException
   * @throws IOException
   * @throws MigrationException
   * @throws ORMException
   */
  public function _before(UnitTester $I, Scenario $scenario): void
  {
    $this->workingDirectory = __DIR__;
    $this->staticMigrationsDirectory = Paths::join($this->workingDirectory, 'static_migrations', 'database', 'migrations');

    $configFilename = Paths::join(__DIR__, 'config', 'default.php');
    $config = require($configFilename);

    if (is_array($config))
    {
      $allMySQLDatabases = $config['databases']['mysql'];
      foreach ($allMySQLDatabases as $databaseName => $dbConfig)
      {
        $this->databaseName = $databaseName;
        $databaseTypeName = $dbConfig['type'] ?? DataSourceType::MYSQL->value;
        $this->dataSource = new DataSource([
          'name' => $this->databaseName,
          'type' => DataSourceType::tryFrom($databaseTypeName),
          'host' => $dbConfig['host'] ?? 'localhost',
          'port' => $dbConfig['port'] ?? 3306,
          'username' => $dbConfig['user'] ?? $dbConfig['username'] ?? '',
          'password' => $dbConfig['pass'] ?? $dbConfig['password'] ?? '',
        ]);

        $this->migrator = new Migrator($this->dataSource, self::MIGRATIONS_DIR);
      }
    }

    $currentCest = $scenario->current('name');
    /** @noinspection SpellCheckingInspection */
    $excludedMethods = ['testTheGeneratemigrationfilenameMethod', 'testTheGenerateMethod'];

    if (!in_array($currentCest, $excludedMethods))
    {
      $statement = $this->dataSource->getClient()->query(<<<QUERY
CREATE TABLE IF NOT EXISTS `$this->databaseName`.`__assegai_schema_migrations` (
  `migration` varchar(50) NOT NULL,
  `ran_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`migration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
QUERY
);
      if (false === $statement->execute())
      {
        throw new ORMException("Failed to create schema migration table.");
      }

      if ($currentCest === 'testTheRun')

      foreach ($this->migrationNames as $migrationName)
      {
        $this->migrator->generate($migrationName, $this->workingDirectory);
      }
    }
  }

  /**
   * @param UnitTester $I
   * @return void
   * @throws ORMException
   */
  public function _after(UnitTester $I): void
  {
    if (false === empty_directory(self::MIGRATIONS_DIR))
    {
      $directory = self::MIGRATIONS_DIR;
      throw new ORMException("Failed to empty migrations directory: $directory");
    }
  }

  /** @noinspection SpellCheckingInspection */
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

    $this->migrator->generate($migrationName, $this->workingDirectory);
    $I->assertFileExists($filename);
  }

  /**
   * @param UnitTester $I
   * @return void
   * @throws MigrationException
   */
  public function testTheRunMethod(UnitTester $I): void
  {
    $this->migrator->runAll($this->staticMigrationsDirectory);
    $staticMigrationFiles = scandir($this->staticMigrationsDirectory);
    $totalMigrations = count($staticMigrationFiles) - 2;
    $I->seeNumRecords($totalMigrations, self::MIGRATIONS_SCHEMA_TABLE_NAME);
  }

  /**
   * @param UnitTester $I
   * @return void
   * @throws MigrationException
   */
  public function testTheRedoMethod(UnitTester $I): void
  {
    $staticMigrationFiles = scandir($this->staticMigrationsDirectory);
    $totalMigrations = count($staticMigrationFiles) - 2;
    $this->migrator->redo(migrationsDirectory: $this->staticMigrationsDirectory);
    $I->seeNumRecords($totalMigrations, self::MIGRATIONS_SCHEMA_TABLE_NAME);
  }

  #[Skip]
  /**
   * @param UnitTester $I
   * @return void
   * @throws MigrationException
   */
  public function testTheRevertMethod(UnitTester $I): void
  {
    $this->migrator->revertAll($this->staticMigrationsDirectory);
    $staticMigrationFiles = scandir($this->staticMigrationsDirectory);
    $totalMigrations = 0;
    $I->seeNumRecords($totalMigrations, self::MIGRATIONS_SCHEMA_TABLE_NAME);
  }

  #[Skip]
  /**
   * @param UnitTester $I
   * @return void
   * @throws MigrationException
   */
  public function testTheListMethod(UnitTester $I): void
  {
    $I->wantToTest("showing all migrations and whether they've been run or not use following command");
    $listOfMigrations = $this->migrator->getListOfMigrationsAsString();
    $I->assertStringContainsString('Migration', $listOfMigrations);
    $I->assertStringContainsString('Ran On', $listOfMigrations);
  }
}
