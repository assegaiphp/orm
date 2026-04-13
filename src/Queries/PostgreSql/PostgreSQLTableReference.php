<?php

namespace Assegai\Orm\Queries\PostgreSql;

use Assegai\Orm\Management\Options\FindOptions;
use Assegai\Orm\Management\Options\FindWhereOptions;
use Assegai\Orm\Queries\Sql\SQLTableReference;

/**
 * PostgreSQL-specific FROM-clause builder.
 */
class PostgreSQLTableReference extends SQLTableReference
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
   * Add a HAVING clause and keep the fluent chain on the PostgreSQL builder path.
   *
   * @param string $condition The HAVING condition to append.
   * @return PostgreSQLHavingClause Returns the PostgreSQL HAVING-clause builder.
   */
  public function having(string $condition): PostgreSQLHavingClause
  {
    return $this->createHavingClause(condition: $condition);
  }

  /**
   * Create the PostgreSQL WHERE-clause builder.
   *
   * @param string|array|FindOptions|FindWhereOptions $condition The condition to compile and append.
   * @return SQLWhereClause Returns the PostgreSQL WHERE-clause builder.
   */
  protected function createWhereClause(string|array|FindOptions|FindWhereOptions $condition): PostgreSQLWhereClause
  {
    return new PostgreSQLWhereClause(query: $this->query, condition: $condition);
  }

  /**
   * Create the PostgreSQL HAVING-clause builder.
   *
   * @param string $condition The HAVING condition to append.
   * @return SQLHavingClause Returns the PostgreSQL HAVING-clause builder.
   */
  protected function createHavingClause(string $condition): PostgreSQLHavingClause
  {
    return new PostgreSQLHavingClause(query: $this->query, condition: $condition);
  }
}
