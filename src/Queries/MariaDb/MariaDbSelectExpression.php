<?php

namespace Assegai\Orm\Queries\MariaDb;

use Assegai\Orm\Queries\MySql\MySQLSelectExpression;
use Assegai\Orm\Queries\Sql\SQLTableReference;

/**
 * MariaDB-specific SELECT expression builder.
 */
class MariaDbSelectExpression extends MySQLSelectExpression
{
  /**
   * Add the FROM clause and keep the fluent chain on the MariaDB builder path.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return MariaDbTableReference Returns the MariaDB table reference builder.
   */
  public function from(array|string $tableReferences): MariaDbTableReference
  {
    return $this->createTableReference(tableReferences: $tableReferences);
  }

  /**
   * Create the MariaDB table reference builder.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return MariaDbTableReference Returns the MariaDB table reference builder.
   */
  protected function createTableReference(array|string $tableReferences): SQLTableReference
  {
    return new MariaDbTableReference(query: $this->query, tableReferences: $tableReferences);
  }
}
