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
    $this->query->setQueryString(
      queryString: 'ALTER DATABASE ' . $this->query->quoteIdentifier($databaseName)
    );

    return new MariaDbAlterDatabaseOption(query: $this->query);
  }

  /**
   * Begin an ALTER TABLE statement using the MariaDB-specific table option builder.
   *
   * @param string $tableName The table being altered.
   * @return MariaDbAlterTableOption Returns the MariaDB alter-table option builder.
   */
  public function table(string $tableName): MariaDbAlterTableOption
  {
    $this->query->setQueryString(
      queryString: 'ALTER TABLE ' . $this->query->quoteIdentifier($tableName)
    );

    return new MariaDbAlterTableOption(query: $this->query);
  }
}
