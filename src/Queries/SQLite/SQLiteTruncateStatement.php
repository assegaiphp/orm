<?php

namespace Assegai\Orm\Queries\SQLite;

use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Queries\Sql\SQLQuery;
use Assegai\Orm\Queries\Sql\SQLTruncateStatement;
use Assegai\Orm\Util\SqlDialectHelper;

/**
 * SQLite-specific table-clearing statement builder.
 *
 * SQLite does not support TRUNCATE TABLE directly, so this builder emits the
 * closest supported equivalent.
 */
class SQLiteTruncateStatement extends SQLTruncateStatement
{
  /**
   * Create a SQLite truncate-equivalent statement.
   *
   * @param SQLQuery $query The query instance being built.
   * @param string $tableName The table to clear.
   */
  public function __construct(
    protected readonly SQLQuery $query,
    protected readonly string $tableName,
  ) {
    $quotedTableName = SqlDialectHelper::quoteIdentifier($this->tableName, SQLDialect::SQLITE);
    $this->query->setQueryString("DELETE FROM $quotedTableName");
  }
}
