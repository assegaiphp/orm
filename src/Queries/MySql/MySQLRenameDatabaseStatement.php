<?php

namespace Assegai\Orm\Queries\MySql;

use Assegai\Orm\Queries\Sql\SQLQuery;
use Assegai\Orm\Queries\Sql\SQLQueryResult;

/**
 * Legacy MySQL-family database rename helper.
 *
 * This helper preserves the historical two-step rendering used by the ORM for
 * older rename-database flows.
 */
class MySQLRenameDatabaseStatement
{
  private string $queryString = '';

  /**
   * Create a database rename helper.
   *
   * @param SQLQuery $query The query instance that receives the rendered SQL.
   * @param string $oldDbName The current database name.
   * @param string $newDbName The target database name.
   */
  public function __construct(
    private readonly SQLQuery $query,
    private readonly string $oldDbName,
    private readonly string $newDbName,
  ) {
    $this->queryString = "CREATE DATABASE `{$newDbName}` / DROP DATABASE `{$oldDbName}`";
    $this->query->setQueryString($this->queryString);
  }

  /**
   * Execute the underlying rename helper statement.
   *
   * @return SQLQueryResult Returns the query execution result.
   */
  public function execute(): SQLQueryResult
  {
    return $this->query->execute();
  }
}
