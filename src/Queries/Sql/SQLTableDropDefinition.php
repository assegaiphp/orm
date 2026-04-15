<?php

namespace Assegai\Orm\Queries\Sql;

/**
 * Shared DROP entry point for SQL dialects that support dropping tables.
 */
class SQLTableDropDefinition implements SQLDropDefinitionInterface
{
  /**
   * Creates a shared table-capable DROP definition bound to the supplied query root.
   *
   * @param SQLQuery $query Receives the rendered DROP statement fragments.
   */
  public function __construct(protected readonly SQLQuery $query)
  {
  }

  /**
   * Begins a DROP TABLE statement.
   *
   * @param string $tableName The table name to drop.
   * @return SQLDropTableStatement Returns the DROP TABLE statement builder.
   */
  public function table(string $tableName): SQLDropTableStatement
  {
    return $this->createDropTableStatement(tableName: $tableName);
  }

  /**
   * Creates the DROP TABLE statement builder for the active SQL dialect.
   *
   * @param string $tableName The table name to drop.
   * @return SQLDropTableStatement Returns the DROP TABLE statement builder.
   */
  protected function createDropTableStatement(string $tableName): SQLDropTableStatement
  {
    return new SQLDropTableStatement(query: $this->query, tableName: $tableName);
  }
}
