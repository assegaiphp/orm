<?php

namespace Assegai\Orm\Queries\MariaDb;

use Assegai\Orm\Management\Options\FindOptions;
use Assegai\Orm\Management\Options\FindWhereOptions;
use Assegai\Orm\Queries\MySql\MySQLAssignmentList;

/**
 * MariaDB-specific SET-clause builder.
 */
class MariaDbAssignmentList extends MySQLAssignmentList
{
  /**
   * Add a WHERE clause and keep the fluent chain on the MariaDB builder path.
   *
   * @param string|array|FindOptions|FindWhereOptions $condition The condition to compile and append.
   * @return MariaDbWhereClause Returns the MariaDB WHERE-clause builder.
   */
  public function where(string|array|FindOptions|FindWhereOptions $condition): MariaDbWhereClause
  {
    return $this->createWhereClause(condition: $condition);
  }

  /**
   * Create the MariaDB WHERE-clause builder.
   *
   * @param string|array|FindOptions|FindWhereOptions $condition The condition to compile and append.
   * @return MariaDbWhereClause Returns the MariaDB WHERE-clause builder.
   */
  protected function createWhereClause(string|array|FindOptions|FindWhereOptions $condition): MariaDbWhereClause
  {
    return new MariaDbWhereClause(query: $this->query, condition: $condition);
  }
}
