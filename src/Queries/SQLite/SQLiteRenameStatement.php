<?php

namespace Assegai\Orm\Queries\SQLite;

use Assegai\Orm\Queries\Sql\SQLRenameStatement;

/**
 * SQLite-specific RENAME statement builder.
 */
class SQLiteRenameStatement extends SQLRenameStatement
{
  /**
   * Begin a SQLite table rename operation.
   *
   * @param string $from The current table name.
   * @param string $to The new table name.
   * @return SQLiteRenameTableStatement Returns the SQLite rename-table builder.
   */
  public function table(string $from, string $to): SQLiteRenameTableStatement
  {
    return new SQLiteRenameTableStatement(query: $this->query, oldTableName: $from, newTableName: $to);
  }
}