<?php

namespace Assegai\Orm\Queries\MySql;

use Assegai\Orm\Queries\Sql\SQLCreateTableStatement;
use Assegai\Orm\Queries\Sql\SQLTableOptions;

/**
 * MySQL-specific CREATE TABLE statement builder.
 */
class MySQLCreateTableStatement extends SQLCreateTableStatement
{
  /**
   * Creates the MySQL table-options builder for the current CREATE TABLE statement.
   *
   * @param array<int, mixed> $columns The column definitions to render into the CREATE TABLE body.
   * @return SQLTableOptions Returns the MySQL table-options builder.
   */
  protected function createTableOptions(array $columns): SQLTableOptions
  {
    return new MySQLTableOptions(query: $this->query, columns: $columns);
  }
}
