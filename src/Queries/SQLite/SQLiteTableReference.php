<?php

namespace Assegai\Orm\Queries\SQLite;

use Assegai\Orm\Management\Options\FindOptions;
use Assegai\Orm\Management\Options\FindWhereOptions;
use Assegai\Orm\Queries\Sql\SQLTableReference;

/**
 * SQLite-specific FROM-clause builder.
 */
class SQLiteTableReference extends SQLTableReference
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
   * Add a HAVING clause and keep the fluent chain on the SQLite builder path.
   *
   * @param string $condition The HAVING condition to append.
   * @return SQLiteHavingClause Returns the SQLite HAVING-clause builder.
   */
  public function having(string $condition): SQLiteHavingClause
  {
    return $this->createHavingClause(condition: $condition);
  }

  /**
   * Create the SQLite WHERE-clause builder.
   *
   * @param string|array|FindOptions|FindWhereOptions $condition The condition to compile and append.
   * @return SQLWhereClause Returns the SQLite WHERE-clause builder.
   */
  protected function createWhereClause(string|array|FindOptions|FindWhereOptions $condition): SQLiteWhereClause
  {
    return new SQLiteWhereClause(query: $this->query, condition: $condition);
  }

  /**
   * Create the SQLite HAVING-clause builder.
   *
   * @param string $condition The HAVING condition to append.
   * @return SQLHavingClause Returns the SQLite HAVING-clause builder.
   */
  protected function createHavingClause(string $condition): SQLiteHavingClause
  {
    return new SQLiteHavingClause(query: $this->query, condition: $condition);
  }
}
