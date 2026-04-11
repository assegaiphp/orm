<?php

namespace Assegai\Orm\Queries\MySql;

use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Queries\Sql\SQLQuery;
use Assegai\Orm\Traits\ExecutableTrait;
use Assegai\Orm\Util\SqlDialectHelper;

/**
 * MySQL-specific USE statement builder.
 *
 * Switches the active database for the current MySQL connection.
 */
class MySQLUseStatement
{
  use ExecutableTrait;

  /**
   * Create a MySQL USE statement.
   *
   * @param SQLQuery $query The query instance being built.
   * @param string $dbName The database name to switch to.
   */
  public function __construct(
    private readonly SQLQuery $query,
    private readonly string $dbName,
  ) {
    $quotedDatabaseName = SqlDialectHelper::quoteIdentifier($this->dbName, SQLDialect::MYSQL);
    $this->query->setQueryString("USE $quotedDatabaseName");
  }
}
