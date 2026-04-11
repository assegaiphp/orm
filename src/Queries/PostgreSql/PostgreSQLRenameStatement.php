<?php

namespace Assegai\Orm\Queries\PostgreSql;

use Assegai\Orm\Queries\Sql\SQLRenameStatement;

/**
 * PostgreSQL-specific RENAME statement builder.
 */
class PostgreSQLRenameStatement extends SQLRenameStatement
{
  /**
   * Begin a PostgreSQL table rename operation.
   *
   * @param string $from The current table name.
   * @param string $to The new table name.
   * @return PostgreSQLRenameTableStatement Returns the PostgreSQL rename-table builder.
   */
  public function table(string $from, string $to): PostgreSQLRenameTableStatement
  {
    return new PostgreSQLRenameTableStatement(query: $this->query, oldTableName: $from, newTableName: $to);
  }
}