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
    $this->query->setQueryString(
      queryString: 'ALTER DATABASE ' . $this->query->quoteIdentifier($databaseName)
    );

    return new MySQLAlterDatabaseOption(query: $this->query);
  }

  /**
   * Begin an ALTER TABLE statement using the MySQL-specific table option builder.
   *
   * @param string $tableName The table being altered.
   * @return MySQLAlterTableOption Returns the MySQL alter-table option builder.
   */
  public function table(string $tableName): MySQLAlterTableOption
  {
    $this->query->setQueryString(
      queryString: 'ALTER TABLE ' . $this->query->quoteIdentifier($tableName)
    );

    return new MySQLAlterTableOption(query: $this->query);
  }
}
