<?php

namespace Assegai\Orm\Migrations;

use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\Exceptions\MigrationException;
use PDO;
use Stringable;

/**
 * The MigrationsList class is responsible for loading and returning a list of migrations from the database.
 */
class MigrationsList implements Stringable
{
  /**
   * @var SchemaMigrationsEntity[] Array to store the list of migrations.
   */
  protected array $listOfMigrations = [];

  /**
   * Constructs a new MigrationsList instance.
   *
   * @param DataSource $dataSource The data source to use.
   */
  public function __construct(protected DataSource $dataSource)
  {
  }

  /**
   * Loads the list of migrations from the database.
   *
   * @return $this The current instance of MigrationsList.
   * @throws MigrationException Thrown if there is an error loading the list of migrations.
   */
  public function loadList(): self
  {
    $databaseName = $this->dataSource->getDatabaseName();
    if ($databaseName)
    {
      $databaseName = "`$databaseName`.";
    }

    $sql = "SELECT * FROM $databaseName`__assegai_schema_migrations` ORDER BY `ran_on` DESC";
    $statement = $this->dataSource->db->query($sql);

    if (false === $statement || false === $statement->execute())
    {
      throw new MigrationException("Failed to load migrations list. Data Source: " . $this->dataSource->getDatabaseName());
    }

    $this->listOfMigrations = $statement->fetchAll(PDO::FETCH_CLASS, SchemaMigrationsEntity::class);

    return $this;
  }

  /**
   * Returns the list of migrations as a string.
   *
   * @return string A string representation of the list of migrations.
   */
  public function __toString(): string
  {
    $consoleWidth = 120;
    $timestampColumnWidth = 25;
    $migrationNameColumnWidth = $consoleWidth - $timestampColumnWidth;
    $ruleLength = $consoleWidth;

    $format = "%-{$migrationNameColumnWidth}s| %-{$timestampColumnWidth}s|" . PHP_EOL;
    $output = str_repeat('-', $ruleLength) . PHP_EOL;
    $output .= sprintf($format, 'Migration', 'Ran On');
    $output .= str_repeat('-', $ruleLength) . PHP_EOL;

    foreach ($this->listOfMigrations as $migration)
    {
      $output .= sprintf($format, $migration->name, $migration->ranOn);
    }
    $output .= str_repeat('-', $ruleLength) . PHP_EOL;

    return $output;
  }
}