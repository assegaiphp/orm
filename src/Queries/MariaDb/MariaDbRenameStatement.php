<?php

namespace Assegai\Orm\Queries\MariaDb;

use Assegai\Orm\Queries\MySql\MySQLRenameStatement;

/**
 * MariaDB-specific RENAME statement builder.
 *
 * MariaDB currently shares the same rename entrypoint shape as MySQL while
 * returning MariaDB-specific rename-table builders.
 */
class MariaDbRenameStatement extends MySQLRenameStatement
{
  /**
   * Begin a MariaDB table rename operation.
   *
   * @param string $from The current table name.
   * @param string $to The new table name.
   * @return MariaDbRenameTableStatement Returns the MariaDB rename-table builder.
   */
  public function table(string $from, string $to): MariaDbRenameTableStatement
  {
    return new MariaDbRenameTableStatement(query: $this->query, oldTableName: $from, newTableName: $to);
  }
}
