<?php

namespace Assegai\Orm\Queries\Sql;

/**
 * Shared DROP entry point for SQL dialects that also support dropping databases.
 */
class SQLDropDefinition extends SQLTableDropDefinition implements SQLDatabaseDropDefinitionInterface
{
  /**
   * Begins a DROP DATABASE statement.
   *
   * @param string $dbName The database name to drop.
   * @return SQLDropDatabaseStatement Returns the shared DROP DATABASE statement builder.
   */
  public function database(string $dbName): SQLDropDatabaseStatement
  {
    return $this->createDropDatabaseStatement(dbName: $dbName);
  }

  /**
   * Creates the DROP DATABASE statement builder for the active SQL dialect.
   *
   * @param string $dbName The database name to drop.
   * @return SQLDropDatabaseStatement Returns the DROP DATABASE statement builder.
   */
  protected function createDropDatabaseStatement(string $dbName): SQLDropDatabaseStatement
  {
    return new SQLDropDatabaseStatement(query: $this->query, dbName: $dbName);
  }
}
