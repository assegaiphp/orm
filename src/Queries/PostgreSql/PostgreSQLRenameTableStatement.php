<?php

namespace Assegai\Orm\Queries\PostgreSql;

use Assegai\Orm\Queries\Sql\SQLRenameTableStatement;

/**
 * PostgreSQL-specific table rename statement.
 *
 * PostgreSQL uses the `ALTER TABLE ... RENAME TO ...` form for table renames.
 */
class PostgreSQLRenameTableStatement extends SQLRenameTableStatement
{
  /**
   * Build the PostgreSQL-specific SQL string for the rename operation.
   *
   * @return string Returns the compiled PostgreSQL rename SQL.
   */
  protected function buildRenameTableQuery(): string
  {
    $quotedOldTableName = $this->query->quoteIdentifier($this->oldTableName);
    $quotedNewTableName = $this->query->quoteIdentifier($this->newTableName);

    return "ALTER TABLE $quotedOldTableName RENAME TO $quotedNewTableName";
  }
}