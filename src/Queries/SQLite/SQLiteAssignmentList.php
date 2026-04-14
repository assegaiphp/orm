<?php

namespace Assegai\Orm\Queries\SQLite;

use Assegai\Orm\Management\Options\FindOptions;
use Assegai\Orm\Management\Options\FindWhereOptions;
use Assegai\Orm\Queries\Sql\SQLAssignmentList;

/**
 * SQLite-specific SET-clause builder.
 */
class SQLiteAssignmentList extends SQLAssignmentList
{
  /**
   * Add a WHERE clause and keep the fluent chain on the SQLite builder path.
   *
   * @param string|array|FindOptions|FindWhereOptions $condition The condition to compile and append.
   * @return SQLiteWhereClause Returns the SQLite WHERE-clause builder.
   */
  public function where(string|array|FindOptions|FindWhereOptions $condition): SQLiteWhereClause
  {
    return $this->createWhereClause(condition: $condition);
  }

  /**
   * Create the SQLite WHERE-clause builder.
   *
   * @param string|array|FindOptions|FindWhereOptions $condition The condition to compile and append.
   * @return SQLiteWhereClause Returns the SQLite WHERE-clause builder.
   */
  protected function createWhereClause(string|array|FindOptions|FindWhereOptions $condition): SQLiteWhereClause
  {
    return new SQLiteWhereClause(query: $this->query, condition: $condition);
  }
}
