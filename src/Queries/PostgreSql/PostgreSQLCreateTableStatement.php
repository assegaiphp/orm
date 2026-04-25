<?php

namespace Assegai\Orm\Queries\PostgreSql;

use Assegai\Orm\Queries\Sql\SQLCreateTableStatement;
use Assegai\Orm\Queries\Sql\SQLTableOptions;

/**
 * PostgreSQL-specific CREATE TABLE statement builder.
 */
class PostgreSQLCreateTableStatement extends SQLCreateTableStatement
{
  /**
   * Creates the PostgreSQL table-options builder for the current CREATE TABLE statement.
   *
   * @param array<int, mixed> $columns The column definitions to render into the CREATE TABLE body.
   * @return SQLTableOptions Returns the PostgreSQL table-options builder.
   */
  protected function createTableOptions(array $columns): SQLTableOptions
  {
    return new PostgreSQLTableOptions(query: $this->query, columns: $columns);
  }
}
