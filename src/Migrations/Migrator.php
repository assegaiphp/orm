<?php

namespace Assegai\Orm\Migrations;

use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\Exceptions\IOException;
use Assegai\Orm\Exceptions\MigrationException;

/**
 * Class for handling the processing of 'Migration' objects
 */
readonly class Migrator
{
  /**
   * Migrator constructor.
   *
   * @param string $migrationsDirectory Directory containing all migrations
   * @throws IOException
   */
  public function __construct(
    private DataSource $dataSource,
    private string $migrationsDirectory
  )
  {
    if (!is_dir($this->migrationsDirectory))
    {
      throw new IOException("Directory not found: " . $this->migrationsDirectory);
    }
  }

  /**
   * Method to run a specified migration
   *
   * @param Migration $migration The migration to run
   * @return void
   */
  public function run(Migration $migration): void
  {
    $migration->up($this->dataSource);
  }

  /**
   * Method to revert a specified migration
   *
   * @param Migration $migration The migration to revert
   * @return void
   */
  public function revert(Migration $migration): void
  {
    $migration->down($this->dataSource);
  }

  /**
   * Method to generate a new migration file
   *
   * @param string $name The name of the migration file to generate
   * @param string|null $targetDirectory The directory containing the migrations. Defaults to null.
   * @return void
   * @throws MigrationException If the specified target directory is not a valid directory or the migration file
   * could not be created.
   */
  public function generate(string $name, ?string $targetDirectory = null): void
  {
    // Set the target directory
    $targetDirectory = $targetDirectory ?? getcwd();
    $targetDirectory = rtrim($targetDirectory, " \t\n\r\0\x0B" . DIRECTORY_SEPARATOR);

    if (!str_ends_with($targetDirectory, 'database' . DIRECTORY_SEPARATOR . 'migrations'))
    {
      $targetDirectory .= DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations';
    }

    // Validate the target directory
    if (!is_dir($targetDirectory))
    {
      throw new MigrationException("Target directory $targetDirectory is not a valid directory.");
    }

    // Generate the migration file name
    $filename = $this->generateMigrationFileName($name);
    $classname = $this->generateMigrationClassName($name);

    // Build the migration class content
    $content = <<<EOF
<?php

use Assegai\Orm\DataSource\DataSource;

class $classname extends Migration
{
  public function up(DataSource \$dataSource): void
  {
    // TODO: implement the $classname::up() method
  }
  public function down(DataSource \$dataSource): void
  {
    // TODO: implement the $classname::down() method
  }
}
EOF;

    // Write the migration class file
    $filepath = $targetDirectory . DIRECTORY_SEPARATOR . $filename;
    if (!file_put_contents($filepath, $content))
    {
      throw new MigrationException("Could not create migration file $filepath.");
    }
  }

  /**
   * Method to redo a specified migration
   *
   * @param Migration $migration The migration to redo
   * @return void
   */
  public function redo(Migration $migration): void
  {
    $this->revert($migration);
    $this->run($migration);
  }

  /**
   * Method to run all migrations in the migrations directory
   *
   * @param string|null $migrationsDirectory The directory containing the migrations. Defaults to null.
   * @return void
   */
  public function runAll(?string $migrationsDirectory = null): void
  {
    $migrations = $this->getMigrations($migrationsDirectory);
    foreach ($migrations as $migration)
    {
      $this->run($migration);
    }
  }

  /**
   * Method to revert all migrations in the migrations directory
   *
   * @param string|null $migrationsDirectory The directory containing the migrations. Defaults to null.
   * @return void
   */
  public function revertAll(?string $migrationsDirectory = null): void
  {
    $migrations = array_reverse($this->getMigrations($migrationsDirectory));
    foreach ($migrations as $migration)
    {
      $this->revert($migration);
    }
  }

  /**
   * Helper method to retrieve all migrations in the migrations directory
   * @param string|null $migrationsDirectory The directory containing the migrations. Defaults to null.
   * @return array Array of Migration objects
   */
  private function getMigrations(?string $migrationsDirectory = null): array
  {
    $migrations = [];
    // code to retrieve all migrations in the migrations directory and store them in the $migrations array

    $fileNames = scandir($migrationsDirectory ?? $this->migrationsDirectory);
    $fileNames = array_slice($fileNames, 2);

    foreach ($fileNames as $fileName)
    {
      $path = $this->migrationsDirectory . DIRECTORY_SEPARATOR . $fileName;
      $instance = require($path);

      error_log(var_export([
        'path' => $path,
        'instance' => $instance,
      ], true) . PHP_EOL);
//      if ($instance instanceof Migration)
//      {
//
//      }
    }

    return $migrations;
  }

  /**
   * Generates a migration name with the current timestamp prefix and snake cased version of the input name.
   *
   * @param string $name The name of the migration.
   * @param int|null $timestamp The timestamp to use for generating the prefix. If null, the current time will be used.
   *
   * @return string The generated migration file name.
   */
  public function generateMigrationFileName(string $name, ?int $timestamp = null): string
  {
    $timestamp = $this->generateMigrationNamePrefix($timestamp);
    $resolvedName = strtosnake($name);

    return $timestamp . $resolvedName . '.php';
  }

  /**
   * Generates a class name for the migration based on the given name.
   *
   * @param string $name The name of the migration.
   *
   * @return string The generated class name.
   */
  protected function generateMigrationClassName(string $name): string
  {
    return strtopascal($name);
  }

  /**
   * Returns a prefix string for the migration name, formatted as "YYYY_mm_dd_HHiiss_".
   * If a timestamp is provided, the prefix will be generated based on that time, otherwise it will use the current time.
   *
   * @param int|null $timestamp The timestamp to use for generating the prefix. If null, the current time will be used.
   * @return string The generated migration name prefix.
   */
  public function generateMigrationNamePrefix(?int $timestamp = null): string
  {
    return date('Y_m_d_His_', $timestamp);
  }
}
