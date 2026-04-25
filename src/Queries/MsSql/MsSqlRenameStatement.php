<?php

namespace Assegai\Orm\Queries\MsSql;

use Assegai\Orm\Queries\Sql\SQLRenameStatement;
use Assegai\Orm\Queries\Sql\SQLRenameTableStatement;

/**
 * MSSQL-specific RENAME entry point.
 */
class MsSqlRenameStatement extends SQLRenameStatement
{
  /**
   * Begin a table rename operation using MSSQL-specific fluent builders.
   *
   * @param string $from The current table name.
   * @param string $to The new table name.
   * @return MsSqlRenameTableStatement Returns the MSSQL rename-table builder.
   */
  public function table(string $from, string $to): MsSqlRenameTableStatement
  {
    return parent::table($from, $to);
  }

  /**
   * Create the MSSQL rename-table builder.
   *
   * @param string $from The current table name.
   * @param string $to The new table name.
   * @return SQLRenameTableStatement Returns the MSSQL rename-table builder.
   */
  protected function createRenameTableStatement(string $from, string $to): SQLRenameTableStatement
  {
    return new MsSqlRenameTableStatement(query: $this->query, oldTableName: $from, newTableName: $to);
  }
}
