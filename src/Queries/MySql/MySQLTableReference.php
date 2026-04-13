<?php

namespace Assegai\Orm\Queries\MySql;

use Assegai\Orm\Management\Options\FindOptions;
use Assegai\Orm\Management\Options\FindWhereOptions;
use Assegai\Orm\Queries\Sql\SQLTableReference;

/**
 * MySQL-specific FROM-clause builder.
 */
class MySQLTableReference extends SQLTableReference
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
   * Add a HAVING clause and keep the fluent chain on the MySQL builder path.
   *
   * @param string $condition The HAVING condition to append.
   * @return MySQLHavingClause Returns the MySQL HAVING-clause builder.
   */
  public function having(string $condition): MySQLHavingClause
  {
    return $this->createHavingClause(condition: $condition);
  }

  /**
   * Create the MySQL WHERE-clause builder.
   *
   * @param string|array|FindOptions|FindWhereOptions $condition The condition to compile and append.
   * @return SQLWhereClause Returns the MySQL WHERE-clause builder.
   */
  protected function createWhereClause(string|array|FindOptions|FindWhereOptions $condition): MySQLWhereClause
  {
    return new MySQLWhereClause(query: $this->query, condition: $condition);
  }

  /**
   * Create the MySQL HAVING-clause builder.
   *
   * @param string $condition The HAVING condition to append.
   * @return SQLHavingClause Returns the MySQL HAVING-clause builder.
   */
  protected function createHavingClause(string $condition): MySQLHavingClause
  {
    return new MySQLHavingClause(query: $this->query, condition: $condition);
  }
}
