<?php

namespace Assegai\Orm\Queries\SQLite;

use Assegai\Orm\Queries\Sql\SQLDescribeStatement;

/**
 * SQLite-specific describe statement builder.
 *
 * SQLite exposes table metadata through PRAGMA table_info.
 */
class SQLiteDescribeStatement extends SQLDescribeStatement
{
  /**
   * Build the SQLite PRAGMA used for table description.
   *
   * @return string Returns the rendered SQLite describe query.
   */
  protected function buildQueryString(): string
  {
    return 'PRAGMA table_info(' . $this->buildSubjectExpression() . ')';
  }
}
