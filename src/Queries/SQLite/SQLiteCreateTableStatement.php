<?php

namespace Assegai\Orm\Queries\SQLite;

use Assegai\Orm\Queries\Sql\SQLCreateTableStatement;
use Assegai\Orm\Queries\Sql\SQLTableOptions;

/**
 * SQLite-specific CREATE TABLE statement builder.
 */
class SQLiteCreateTableStatement extends SQLCreateTableStatement
{
  /**
   * Creates the SQLite table-options builder for the current CREATE TABLE statement.
   *
   * @param array<int, mixed> $columns The column definitions to render into the CREATE TABLE body.
   * @return SQLTableOptions Returns the SQLite table-options builder.
   */
  protected function createTableOptions(array $columns): SQLTableOptions
  {
    return new SQLiteTableOptions(query: $this->query, columns: $columns);
  }
}
