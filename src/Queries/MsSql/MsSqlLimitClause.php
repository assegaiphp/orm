<?php

namespace Assegai\Orm\Queries\MsSql;

use Assegai\Orm\Queries\Sql\SQLLimitClause;

/**
 * MSSQL-specific paging clause builder.
 */
class MsSqlLimitClause extends SQLLimitClause
{
  /**
   * Build the SQL Server paging clause, adding a deterministic ORDER BY
   * fallback when the query does not already define one.
   *
   * @return string Returns the rendered SQL Server paging clause.
   */
  protected function buildQueryString(): string
  {
    $pagingClause = parent::buildQueryString();

    if ($this->queryAlreadyOrdersResults()) {
      return $pagingClause;
    }

    return "ORDER BY (SELECT 0) {$pagingClause}";
  }

  /**
   * Build the SQL Server paging fragment used when only a limit value is present.
   *
   * @return string Returns the rendered SQL Server paging fragment.
   */
  protected function buildLimitOnlyQueryString(): string
  {
    return "OFFSET 0 ROWS FETCH NEXT {$this->limit} ROWS ONLY";
  }

  /**
   * Build the SQL Server paging fragment used when both limit and offset values are present.
   *
   * @return string Returns the rendered SQL Server paging fragment.
   */
  protected function buildOffsetLimitQueryString(): string
  {
    return "OFFSET {$this->offset} ROWS FETCH NEXT {$this->limit} ROWS ONLY";
  }

  /**
   * Determine whether the owning SQL statement already contains an ORDER BY clause.
   *
   * @return bool Returns true when the statement already orders the result set.
   */
  protected function queryAlreadyOrdersResults(): bool
  {
    return preg_match('/\bORDER\s+BY\b/i', $this->query->queryString()) === 1;
  }
}
