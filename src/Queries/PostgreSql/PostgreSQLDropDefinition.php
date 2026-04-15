<?php

namespace Assegai\Orm\Queries\PostgreSql;

use Assegai\Orm\Queries\Sql\SQLDatabaseDropDefinitionInterface;
use Assegai\Orm\Queries\Sql\SQLDropTableStatement;
use Assegai\Orm\Queries\Sql\SQLTableDropDefinition;

/**
 * PostgreSQL-specific DROP entry point.
 */
class PostgreSQLDropDefinition extends SQLTableDropDefinition implements SQLDatabaseDropDefinitionInterface
{
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
    return $this->createDropTableStatement(tableName: $tableName);
  }

  /**
   * Creates the PostgreSQL DROP TABLE statement builder.
   *
   * @param string $tableName The table name to drop.
   * @return PostgreSQLDropTableStatement Returns the PostgreSQL DROP TABLE statement builder.
   */
  protected function createDropTableStatement(string $tableName): SQLDropTableStatement
  {
    return new PostgreSQLDropTableStatement(query: $this->query, tableName: $tableName);
  }
}
