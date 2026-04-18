<?php

namespace Assegai\Orm\Queries\MsSql;

use Assegai\Orm\Queries\Sql\SQLWhereClause;

/**
 * MSSQL-specific WHERE-clause builder.
 */
class MsSqlWhereClause extends SQLWhereClause
{
  /**
   * Add a paging clause and keep the fluent chain on the MSSQL builder path.
   *
   * @param int $limit The maximum number of rows to return.
   * @param int|null $offset The number of rows to skip before returning results.
   * @return MsSqlLimitClause Returns the MSSQL paging builder.
   */
  public function limit(int $limit, ?int $offset = null): MsSqlLimitClause
  {
    return $this->createLimitClause(limit: $limit, offset: $offset);
  }

  /**
   * Create the MSSQL paging builder.
   *
   * @param int $limit The maximum number of rows to return.
   * @param int|null $offset The number of rows to skip before returning results.
   * @return MsSqlLimitClause Returns the MSSQL paging builder.
   */
  protected function createLimitClause(int $limit, ?int $offset = null): MsSqlLimitClause
  {
    return new MsSqlLimitClause(query: $this->query, limit: $limit, offset: $offset);
  }
}
