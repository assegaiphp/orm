<?php

namespace Assegai\Orm\Queries\PostgreSql;

use Assegai\Orm\Queries\Sql\SQLDatabaseDropDefinitionInterface;
use Assegai\Orm\Queries\Sql\SQLQuery;

/**
 * PostgreSQL-specific DROP entry point.
 */
class PostgreSQLDropDefinition implements SQLDatabaseDropDefinitionInterface
{
  /**
   * Creates a PostgreSQL DROP definition bound to the supplied query root.
   *
   * @param SQLQuery $query Receives the rendered DROP statement fragments.
   */
  public function __construct(private readonly SQLQuery $query)
  {
  }

  /**
   * Begins a PostgreSQL DROP DATABASE statement.
   *
   * @param string $dbName The database name to drop.
   * @param bool $checkIfExists Indicates whether IF EXISTS should be emitted.
   * @param bool $force Indicates whether WITH (FORCE) should be emitted.
   * @return PostgreSQLDropDatabaseStatement Returns the PostgreSQL DROP DATABASE statement builder.
   */
  public function database(
    string $dbName,
    bool $checkIfExists = false,
    bool $force = false,
  ): PostgreSQLDropDatabaseStatement
  {
    return new PostgreSQLDropDatabaseStatement(
      query: $this->query,
      dbName: $dbName,
      checkIfExists: $checkIfExists,
      force: $force,
    );
  }

  /**
   * Begins a PostgreSQL DROP TABLE statement.
   *
   * @param string $tableName The table name to drop.
   * @return PostgreSQLDropTableStatement Returns the PostgreSQL DROP TABLE statement builder.
   */
  public function table(string $tableName): PostgreSQLDropTableStatement
  {
    return new PostgreSQLDropTableStatement(query: $this->query, tableName: $tableName);
  }
}
