<?php

namespace Assegai\Orm\Queries\PostgreSql;

use Assegai\Orm\Queries\Sql\SQLLimitClause;

/**
 * PostgreSQL-specific LIMIT-clause builder.
 */
class PostgreSQLLimitClause extends SQLLimitClause
{
  /**
   * Build the PostgreSQL LIMIT/OFFSET fragment.
   *
   * @return string Returns the rendered PostgreSQL LIMIT/OFFSET fragment.
   */
  protected function buildOffsetLimitQueryString(): string
  {
    return "LIMIT {$this->limit} OFFSET {$this->offset}";
  }
}
