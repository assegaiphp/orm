<?php

namespace Assegai\Orm\Queries\PostgreSql;

use Assegai\Orm\Queries\Sql\SQLSelectExpression;
use Assegai\Orm\Queries\Sql\SQLTableReference;

/**
 * PostgreSQL-specific SELECT expression builder.
 */
class PostgreSQLSelectExpression extends SQLSelectExpression
{
  /**
   * Add the FROM clause and keep the fluent chain on the PostgreSQL builder path.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return PostgreSQLTableReference Returns the PostgreSQL table reference builder.
   */
  public function from(array|string $tableReferences): PostgreSQLTableReference
  {
    return $this->createTableReference(tableReferences: $tableReferences);
  }

  /**
   * Create the PostgreSQL table reference builder.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return PostgreSQLTableReference Returns the PostgreSQL table reference builder.
   */
  protected function createTableReference(array|string $tableReferences): SQLTableReference
  {
    return new PostgreSQLTableReference(query: $this->query, tableReferences: $tableReferences);
  }
}
