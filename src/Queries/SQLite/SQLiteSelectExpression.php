<?php

namespace Assegai\Orm\Queries\SQLite;

use Assegai\Orm\Queries\Sql\SQLSelectExpression;
use Assegai\Orm\Queries\Sql\SQLTableReference;

/**
 * SQLite-specific SELECT expression builder.
 */
class SQLiteSelectExpression extends SQLSelectExpression
{
  /**
   * Add the FROM clause and keep the fluent chain on the SQLite builder path.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return SQLiteTableReference Returns the SQLite table reference builder.
   */
  public function from(array|string $tableReferences): SQLiteTableReference
  {
    return $this->createTableReference(tableReferences: $tableReferences);
  }

  /**
   * Create the SQLite table reference builder.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return SQLiteTableReference Returns the SQLite table reference builder.
   */
  protected function createTableReference(array|string $tableReferences): SQLTableReference
  {
    return new SQLiteTableReference(query: $this->query, tableReferences: $tableReferences);
  }
}
