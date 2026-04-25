<?php

namespace Assegai\Orm\Queries\SQLite;

use Assegai\Orm\Queries\Sql\SQLTruncateStatement;

/**
 * SQLite-specific table-clearing statement builder.
 *
 * SQLite does not support TRUNCATE TABLE directly, so this builder emits the
 * closest supported equivalent.
 */
class SQLiteTruncateStatement extends SQLTruncateStatement
{
  /**
   * Build the SQLite truncate-equivalent statement.
   *
   * @return string Returns the rendered SQLite table-clearing statement.
   */
  protected function buildQueryString(): string
  {
    return 'DELETE FROM ' . $this->buildTableExpression();
  }
}
