<?php

namespace Assegai\Orm\Queries\MariaDb;

use Assegai\Orm\Queries\MySql\MySQLWhereClause;

/**
 * MariaDB-specific WHERE-clause builder.
 */
class MariaDbWhereClause extends MySQLWhereClause
{
  /**
   * Add a LIMIT clause and keep the fluent chain on the MariaDB builder path.
   *
   * @param int $limit The maximum number of rows to return.
   * @param int|null $offset The number of rows to skip before returning results.
   * @return MariaDbLimitClause Returns the MariaDB LIMIT-clause builder.
   */
  public function limit(int $limit, ?int $offset = null): MariaDbLimitClause
  {
    return $this->createLimitClause(limit: $limit, offset: $offset);
  }

  /**
   * Create the MariaDB LIMIT-clause builder.
   *
   * @param int $limit The maximum number of rows to return.
   * @param int|null $offset The number of rows to skip before returning results.
   * @return MariaDbLimitClause Returns the MariaDB LIMIT-clause builder.
   */
  protected function createLimitClause(int $limit, ?int $offset = null): MariaDbLimitClause
  {
    return new MariaDbLimitClause(query: $this->query, limit: $limit, offset: $offset);
  }
}
