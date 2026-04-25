<?php

namespace Assegai\Orm\Queries\SQLite;

use Assegai\Orm\Queries\Sql\SQLWhereClause;

/**
 * SQLite-specific WHERE-clause builder.
 */
class SQLiteWhereClause extends SQLWhereClause
{
  /**
   * Add a LIMIT clause and keep the fluent chain on the SQLite builder path.
   *
   * @param int $limit The maximum number of rows to return.
   * @param int|null $offset The number of rows to skip before returning results.
   * @return SQLiteLimitClause Returns the SQLite LIMIT-clause builder.
   */
  public function limit(int $limit, ?int $offset = null): SQLiteLimitClause
  {
    return $this->createLimitClause(limit: $limit, offset: $offset);
  }

  /**
   * Create the SQLite LIMIT-clause builder.
   *
   * @param int $limit The maximum number of rows to return.
   * @param int|null $offset The number of rows to skip before returning results.
   * @return SQLiteLimitClause Returns the SQLite LIMIT-clause builder.
   */
  protected function createLimitClause(int $limit, ?int $offset = null): SQLiteLimitClause
  {
    return new SQLiteLimitClause(query: $this->query, limit: $limit, offset: $offset);
  }
}
