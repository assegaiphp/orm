<?php

namespace Assegai\Orm\Queries\PostgreSql;

use Assegai\Orm\Queries\Sql\SQLWhereClause;

/**
 * PostgreSQL-specific WHERE-clause builder.
 */
class PostgreSQLWhereClause extends SQLWhereClause
{
  /**
   * Add a LIMIT clause and keep the fluent chain on the PostgreSQL builder path.
   *
   * @param int $limit The maximum number of rows to return.
   * @param int|null $offset The number of rows to skip before returning results.
   * @return PostgreSQLLimitClause Returns the PostgreSQL LIMIT-clause builder.
   */
  public function limit(int $limit, ?int $offset = null): PostgreSQLLimitClause
  {
    return $this->createLimitClause(limit: $limit, offset: $offset);
  }

  /**
   * Create the PostgreSQL LIMIT-clause builder.
   *
   * @param int $limit The maximum number of rows to return.
   * @param int|null $offset The number of rows to skip before returning results.
   * @return PostgreSQLLimitClause Returns the PostgreSQL LIMIT-clause builder.
   */
  protected function createLimitClause(int $limit, ?int $offset = null): PostgreSQLLimitClause
  {
    return new PostgreSQLLimitClause(query: $this->query, limit: $limit, offset: $offset);
  }
}
