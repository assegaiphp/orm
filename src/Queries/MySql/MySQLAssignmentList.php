<?php

namespace Assegai\Orm\Queries\MySql;

use Assegai\Orm\Management\Options\FindOptions;
use Assegai\Orm\Management\Options\FindWhereOptions;
use Assegai\Orm\Queries\Sql\SQLAssignmentList;

/**
 * MySQL-specific SET-clause builder.
 */
class MySQLAssignmentList extends SQLAssignmentList
{
  /**
   * Add a WHERE clause and keep the fluent chain on the MySQL builder path.
   *
   * @param string|array|FindOptions|FindWhereOptions $condition The condition to compile and append.
   * @return MySQLWhereClause Returns the MySQL WHERE-clause builder.
   */
  public function where(string|array|FindOptions|FindWhereOptions $condition): MySQLWhereClause
  {
    return $this->createWhereClause(condition: $condition);
  }

  /**
   * Create the MySQL WHERE-clause builder.
   *
   * @param string|array|FindOptions|FindWhereOptions $condition The condition to compile and append.
   * @return MySQLWhereClause Returns the MySQL WHERE-clause builder.
   */
  protected function createWhereClause(string|array|FindOptions|FindWhereOptions $condition): MySQLWhereClause
  {
    return new MySQLWhereClause(query: $this->query, condition: $condition);
  }
}
