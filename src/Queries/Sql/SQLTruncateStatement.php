<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Traits\ExecutableTrait;

/**
 * Base SQL-family truncate statement builder.
 *
 * Dialect-specific subclasses can override the constructor to emit the syntax
 * that matches their own table-clearing semantics.
 */
class SQLTruncateStatement
{
  use ExecutableTrait;

  /**
   * Create a generic TRUNCATE TABLE statement.
   *
   * @param SQLQuery $query The query instance being built.
   * @param string $tableName The table to truncate.
   */
  public function __construct(
    protected readonly SQLQuery $query,
    protected readonly string $tableName,
  ) {
    $this->query->setQueryString(queryString: "TRUNCATE TABLE `$tableName`");
  }
}
