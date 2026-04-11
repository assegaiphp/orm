<?php

namespace Assegai\Orm\Queries\MySql;

use Assegai\Orm\Queries\Sql\SQLRenameStatement;

/**
 * MySQL-specific RENAME statement builder.
 */
class MySQLRenameStatement extends SQLRenameStatement
{
  /**
   * Begin a MySQL table rename operation.
   *
   * @param string $from The current table name.
   * @param string $to The new table name.
   * @return MySQLRenameTableStatement Returns the MySQL rename-table builder.
   */
  public function table(string $from, string $to): MySQLRenameTableStatement
  {
    return new MySQLRenameTableStatement(query: $this->query, oldTableName: $from, newTableName: $to);
  }
}