<?php

namespace Assegai\Orm\Queries\Sql;

/**
 * Shared DROP entry point for generic SQL query builders.
 */
class SQLDropDefinition implements SQLDatabaseDropDefinitionInterface
{
  /**
   * Creates a shared DROP definition bound to the supplied query root.
   *
   * @param SQLQuery $query Receives the rendered DROP statement fragments.
   */
  public function __construct(protected readonly SQLQuery $query)
  {
  }

  /**
   * Begins a DROP DATABASE statement.
   *
   * @param string $dbName The database name to drop.
   * @return SQLDropDatabaseStatement Returns the shared DROP DATABASE statement builder.
   */
  public function database(string $dbName): SQLDropDatabaseStatement
  {
    return new SQLDropDatabaseStatement(query: $this->query, dbName: $dbName);
  }

  /**
   * Begins a DROP TABLE statement.
   *
   * @param string $tableName The table name to drop.
   * @return SQLDropTableStatement Returns the shared DROP TABLE statement builder.
   */
  public function table(string $tableName): SQLDropTableStatement
  {
    return new SQLDropTableStatement(query: $this->query, tableName: $tableName);
  }
}
