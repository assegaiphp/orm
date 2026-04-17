<?php

namespace Assegai\Orm\Queries\MySql;

use Assegai\Orm\Queries\Sql\SQLAlterDefinition;

/**
 * MySQL-specific ALTER builder.
 *
 * MySQL supports both table-level and database-level ALTER statements, so this
 * root extends the shared table fluency with a MySQL-family database path.
 */
class MySQLAlterDefinition extends SQLAlterDefinition
{
  /**
   * Begin an ALTER DATABASE statement using the MySQL-specific option builder.
   *
   * @param string $databaseName The database being altered.
   * @return MySQLAlterDatabaseOption Returns the database alter option builder.
   */
  public function database(string $databaseName): MySQLAlterDatabaseOption
  {
    $this->beginAlterDatabase(databaseName: $databaseName);

    return $this->createAlterDatabaseOption();
  }

  /**
   * Begin an ALTER TABLE statement using the MySQL-specific table option builder.
   *
   * @param string $tableName The table being altered.
   * @return MySQLAlterTableOption Returns the MySQL alter-table option builder.
   */
  public function table(string $tableName): MySQLAlterTableOption
  {
    return parent::table($tableName);
  }

  /**
   * Start the ALTER DATABASE statement for the given database.
   *
   * @param string $databaseName The database being altered.
   * @return void
   */
  protected function beginAlterDatabase(string $databaseName): void
  {
    $this->query->setQueryString(
      queryString: 'ALTER DATABASE ' . $this->query->quoteIdentifier($databaseName)
    );
  }

  /**
   * Create the database alter option builder for this dialect.
   *
   * @return MySQLAlterDatabaseOption Returns the database alter option builder.
   */
  protected function createAlterDatabaseOption(): MySQLAlterDatabaseOption
  {
    return new MySQLAlterDatabaseOption(query: $this->query);
  }

  /**
   * Create the alter-table option builder for this dialect.
   *
   * @return MySQLAlterTableOption Returns the MySQL alter-table option builder.
   */
  protected function createAlterTableOption(): MySQLAlterTableOption
  {
    return new MySQLAlterTableOption(query: $this->query);
  }
}
