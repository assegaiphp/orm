<?php

namespace Assegai\Orm\Queries\MsSql;

use Assegai\Orm\Queries\Sql\SQLLimitClause;

/**
 * MSSQL-specific paging clause builder.
 */
class MsSqlLimitClause extends SQLLimitClause
{
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
}
