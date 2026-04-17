<?php

namespace Assegai\Orm\Queries\MariaDb;

use Assegai\Orm\Queries\MySql\MySQLAlterDefinition;

/**
 * MariaDB-specific ALTER builder.
 *
 * MariaDB keeps the MySQL-family database ALTER path while returning typed
 * MariaDB builders for both database-level and table-level work.
 */
class MariaDbAlterDefinition extends MySQLAlterDefinition
{
  /**
   * Begin an ALTER DATABASE statement using the MariaDB-specific option builder.
   *
   * @param string $databaseName The database being altered.
   * @return MariaDbAlterDatabaseOption Returns the database alter option builder.
   */
  public function database(string $databaseName): MariaDbAlterDatabaseOption
  {
    return parent::database($databaseName);
  }

  /**
   * Begin an ALTER TABLE statement using the MariaDB-specific table option builder.
   *
   * @param string $tableName The table being altered.
   * @return MariaDbAlterTableOption Returns the MariaDB alter-table option builder.
   */
  public function table(string $tableName): MariaDbAlterTableOption
  {
    return parent::table($tableName);
  }

  /**
   * Create the database alter option builder for this dialect.
   *
   * @return MariaDbAlterDatabaseOption Returns the database alter option builder.
   */
  protected function createAlterDatabaseOption(): MariaDbAlterDatabaseOption
  {
    return new MariaDbAlterDatabaseOption(query: $this->query);
  }

  /**
   * Create the alter-table option builder for this dialect.
   *
   * @return MariaDbAlterTableOption Returns the MariaDB alter-table option builder.
   */
  protected function createAlterTableOption(): MariaDbAlterTableOption
  {
    return new MariaDbAlterTableOption(query: $this->query);
  }
}
