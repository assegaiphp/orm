<?php

namespace Assegai\Orm\Queries\MariaDb;

use Assegai\Orm\Queries\MySql\MySQLCreateTableStatement;
use Assegai\Orm\Queries\Sql\SQLTableOptions;

/**
 * MariaDB-specific CREATE TABLE statement builder.
 */
class MariaDbCreateTableStatement extends MySQLCreateTableStatement
{
  /**
   * Creates the MariaDB table-options builder for the current CREATE TABLE statement.
   *
   * @param array<int, mixed> $columns The column definitions to render into the CREATE TABLE body.
   * @return SQLTableOptions Returns the MariaDB table-options builder.
   */
  protected function createTableOptions(array $columns): SQLTableOptions
  {
    return new MariaDbTableOptions(query: $this->query, columns: $columns);
  }
}
