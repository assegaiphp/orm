<?php

namespace Assegai\Orm\Queries\PostgreSql;

use Assegai\Orm\Queries\Sql\SQLDropDatabaseStatement;

/**
 * PostgreSQL-specific DROP DATABASE statement builder.
 */
class PostgreSQLDropDatabaseStatement extends SQLDropDatabaseStatement
{
  /**
   * Creates a PostgreSQL DROP DATABASE statement builder.
   *
   * @param \Assegai\Orm\Queries\Sql\SQLQuery $query Receives the rendered DROP DATABASE statement.
   * @param string $dbName The database name to drop.
   * @param bool $checkIfExists Indicates whether IF EXISTS should be emitted.
   * @param bool $force Indicates whether PostgreSQL should force disconnect active sessions.
   */
  public function __construct(
    \Assegai\Orm\Queries\Sql\SQLQuery $query,
    string $dbName,
    bool $checkIfExists = false,
    protected readonly bool $force = false,
  ) {
    parent::__construct(query: $query, dbName: $dbName, checkIfExists: $checkIfExists);
  }

  /**
   * Builds the PostgreSQL DROP DATABASE statement.
   *
   * @return string Returns the DROP DATABASE statement tailored for PostgreSQL.
   */
  protected function buildQueryString(): string
  {
    $queryString = parent::buildQueryString();

    if ($this->force) {
      $queryString .= ' WITH (FORCE)';
    }

    return $queryString;
  }
}
