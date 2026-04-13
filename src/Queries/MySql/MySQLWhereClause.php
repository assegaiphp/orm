<?php

namespace Assegai\Orm\Queries\MySql;

use Assegai\Orm\Queries\Sql\SQLWhereClause;

/**
 * MySQL-specific WHERE-clause builder.
 */
class MySQLWhereClause extends SQLWhereClause
{
  /**
   * Add a LIMIT clause and keep the fluent chain on the MySQL builder path.
   *
   * @param int $limit The maximum number of rows to return.
   * @param int|null $offset The number of rows to skip before returning results.
   * @return MySQLLimitClause Returns the MySQL LIMIT-clause builder.
   */
  public function limit(int $limit, ?int $offset = null): MySQLLimitClause
  {
    return $this->createLimitClause(limit: $limit, offset: $offset);
  }

  /**
   * Create the MySQL LIMIT-clause builder.
   *
   * @param int $limit The maximum number of rows to return.
   * @param int|null $offset The number of rows to skip before returning results.
   * @return MySQLLimitClause Returns the MySQL LIMIT-clause builder.
   */
  protected function createLimitClause(int $limit, ?int $offset = null): MySQLLimitClause
  {
    return new MySQLLimitClause(query: $this->query, limit: $limit, offset: $offset);
  }
}
