<?php

namespace Assegai\Orm\Queries\MariaDb;

use Assegai\Orm\Management\Options\FindOptions;
use Assegai\Orm\Management\Options\FindWhereOptions;
use Assegai\Orm\Queries\MySql\MySQLTableReference;

/**
 * MariaDB-specific FROM-clause builder.
 */
class MariaDbTableReference extends MySQLTableReference
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
   * Add a HAVING clause and keep the fluent chain on the MariaDB builder path.
   *
   * @param string $condition The HAVING condition to append.
   * @return MariaDbHavingClause Returns the MariaDB HAVING-clause builder.
   */
  public function having(string $condition): MariaDbHavingClause
  {
    return $this->createHavingClause(condition: $condition);
  }

  /**
   * Create the MariaDB WHERE-clause builder.
   *
   * @param string|array|FindOptions|FindWhereOptions $condition The condition to compile and append.
   * @return SQLWhereClause Returns the MariaDB WHERE-clause builder.
   */
  protected function createWhereClause(string|array|FindOptions|FindWhereOptions $condition): MariaDbWhereClause
  {
    return new MariaDbWhereClause(query: $this->query, condition: $condition);
  }

  /**
   * Create the MariaDB HAVING-clause builder.
   *
   * @param string $condition The HAVING condition to append.
   * @return SQLHavingClause Returns the MariaDB HAVING-clause builder.
   */
  protected function createHavingClause(string $condition): MariaDbHavingClause
  {
    return new MariaDbHavingClause(query: $this->query, condition: $condition);
  }
}
