<?php

namespace Assegai\Orm\Queries\MySql;

use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Queries\Sql\SQLQuery;
use Assegai\Orm\Queries\Sql\SQLTruncateStatement;
use Assegai\Orm\Util\SqlDialectHelper;

/**
 * MySQL-specific TRUNCATE TABLE statement builder.
 */
class MySQLTruncateStatement extends SQLTruncateStatement
{
  /**
   * Create a MySQL TRUNCATE TABLE statement.
   *
   * @param SQLQuery $query The query instance being built.
   * @param string $tableName The table to truncate.
   */
  public function __construct(
    protected readonly SQLQuery $query,
    protected readonly string $tableName,
  ) {
    $quotedTableName = SqlDialectHelper::quoteIdentifier($this->tableName, SQLDialect::MYSQL);
    $this->query->setQueryString("TRUNCATE TABLE $quotedTableName");
  }
}
