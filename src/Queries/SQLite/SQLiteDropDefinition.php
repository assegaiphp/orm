<?php

namespace Assegai\Orm\Queries\SQLite;

use Assegai\Orm\Queries\Sql\SQLDropDefinitionInterface;
use Assegai\Orm\Queries\Sql\SQLQuery;

/**
 * SQLite-specific DROP entry point.
 */
class SQLiteDropDefinition implements SQLDropDefinitionInterface
{
  /**
   * Creates a SQLite DROP definition bound to the supplied query root.
   *
   * @param SQLQuery $query Receives the rendered DROP statement fragments.
   */
  public function __construct(private readonly SQLQuery $query)
  {
  }

  /**
   * Begins a SQLite DROP TABLE statement.
   *
   * @param string $tableName The table name to drop.
   * @return SQLiteDropTableStatement Returns the SQLite DROP TABLE statement builder.
   */
  public function table(string $tableName): SQLiteDropTableStatement
  {
    return new SQLiteDropTableStatement(query: $this->query, tableName: $tableName);
  }
}
