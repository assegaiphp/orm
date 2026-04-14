<?php

namespace Assegai\Orm\Queries\PostgreSql;

use Assegai\Orm\Management\Options\FindOptions;
use Assegai\Orm\Management\Options\FindWhereOptions;
use Assegai\Orm\Queries\Sql\SQLAssignmentList;

/**
 * PostgreSQL-specific SET-clause builder.
 */
class PostgreSQLAssignmentList extends SQLAssignmentList
{
  /**
   * Add a WHERE clause and keep the fluent chain on the PostgreSQL builder path.
   *
   * @param string|array|FindOptions|FindWhereOptions $condition The condition to compile and append.
   * @return PostgreSQLWhereClause Returns the PostgreSQL WHERE-clause builder.
   */
  public function where(string|array|FindOptions|FindWhereOptions $condition): PostgreSQLWhereClause
  {
    return $this->createWhereClause(condition: $condition);
  }

  /**
   * Create the PostgreSQL WHERE-clause builder.
   *
   * @param string|array|FindOptions|FindWhereOptions $condition The condition to compile and append.
   * @return PostgreSQLWhereClause Returns the PostgreSQL WHERE-clause builder.
   */
  protected function createWhereClause(string|array|FindOptions|FindWhereOptions $condition): PostgreSQLWhereClause
  {
    return new PostgreSQLWhereClause(query: $this->query, condition: $condition);
  }
}
