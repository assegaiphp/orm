<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Traits\ExecutableTrait;
use Assegai\Orm\Util\SqlIdentifier;

/**
 * Base SQL-family truncate statement builder.
 *
 * Dialect-specific subclasses can override the rendering hooks to emit the
 * syntax that matches their own table-clearing semantics.
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
    $this->query->setQueryString(queryString: $this->buildQueryString());
  }

  /**
   * Build the truncate statement for the active SQL-family builder.
   *
   * @return string Returns the rendered truncate statement.
   */
  protected function buildQueryString(): string
  {
    return 'TRUNCATE TABLE ' . $this->buildTableExpression();
  }

  /**
   * Build the table expression for the active SQL dialect.
   *
   * @return string Returns the quoted table identifier.
   */
  protected function buildTableExpression(): string
  {
    return SqlIdentifier::quote($this->tableName, $this->query->getDialect());
  }
}
