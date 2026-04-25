<?php

namespace Assegai\Orm\Queries\MsSql;

use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Queries\Sql\SQLQuery;
use Assegai\Orm\Traits\ExecutableTrait;
use Assegai\Orm\Util\SqlDialectHelper;

/**
 * MSSQL-specific USE statement builder.
 */
class MsSqlUseStatement
{
  use ExecutableTrait;

  /**
   * Create a SQL Server USE statement.
   *
   * @param SQLQuery $query The query instance being built.
   * @param string $dbName The database name to switch to.
   */
  public function __construct(
    private readonly SQLQuery $query,
    private readonly string $dbName,
  ) {
    $quotedDatabaseName = SqlDialectHelper::quoteIdentifier($this->dbName, SQLDialect::MSSQL);
    $this->query->setQueryString("USE $quotedDatabaseName");
  }
}
