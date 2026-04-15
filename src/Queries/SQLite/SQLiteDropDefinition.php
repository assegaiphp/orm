<?php

namespace Assegai\Orm\Queries\SQLite;

use Assegai\Orm\Queries\Sql\SQLDropDefinitionInterface;
use Assegai\Orm\Queries\Sql\SQLDropTableStatement;
use Assegai\Orm\Queries\Sql\SQLTableDropDefinition;

/**
 * SQLite-specific DROP entry point.
 */
class SQLiteDropDefinition extends SQLTableDropDefinition implements SQLDropDefinitionInterface
{
  /**
   * Begins a SQLite DROP TABLE statement.
   *
   * @param string $tableName The table name to drop.
   * @return SQLiteDropTableStatement Returns the SQLite DROP TABLE statement builder.
   */
  public function table(string $tableName): SQLiteDropTableStatement
  {
    return $this->createDropTableStatement(tableName: $tableName);
  }

  /**
   * Creates the SQLite DROP TABLE statement builder.
   *
   * @param string $tableName The table name to drop.
   * @return SQLiteDropTableStatement Returns the SQLite DROP TABLE statement builder.
   */
  protected function createDropTableStatement(string $tableName): SQLDropTableStatement
  {
    return new SQLiteDropTableStatement(query: $this->query, tableName: $tableName);
  }
}
