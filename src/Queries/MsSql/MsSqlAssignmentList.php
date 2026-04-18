<?php

namespace Assegai\Orm\Queries\MsSql;

use Assegai\Orm\Management\Options\FindOptions;
use Assegai\Orm\Management\Options\FindWhereOptions;
use Assegai\Orm\Queries\Sql\SQLAssignmentList;

/**
 * MSSQL-specific SET-clause builder.
 */
class MsSqlAssignmentList extends SQLAssignmentList
{
  /**
   * Add a WHERE clause and keep the fluent chain on the MSSQL builder path.
   *
   * @param string|array|FindOptions|FindWhereOptions $condition The condition to compile and append.
   * @return MsSqlWhereClause Returns the MSSQL WHERE-clause builder.
   */
  public function where(string|array|FindOptions|FindWhereOptions $condition): MsSqlWhereClause
  {
    return $this->createWhereClause(condition: $condition);
  }

  /**
   * Create the MSSQL WHERE-clause builder.
   *
   * @param string|array|FindOptions|FindWhereOptions $condition The condition to compile and append.
   * @return MsSqlWhereClause Returns the MSSQL WHERE-clause builder.
   */
  protected function createWhereClause(string|array|FindOptions|FindWhereOptions $condition): MsSqlWhereClause
  {
    return new MsSqlWhereClause(query: $this->query, condition: $condition);
  }
}
