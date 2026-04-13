<?php

namespace Assegai\Orm\Queries\MySql;

use Assegai\Orm\Queries\Sql\SQLSelectExpression;
use Assegai\Orm\Queries\Sql\SQLTableReference;

/**
 * MySQL-specific SELECT expression builder.
 */
class MySQLSelectExpression extends SQLSelectExpression
{
  /**
   * Add the FROM clause and keep the fluent chain on the MySQL builder path.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return MySQLTableReference Returns the MySQL table reference builder.
   */
  public function from(array|string $tableReferences): MySQLTableReference
  {
    return $this->createTableReference(tableReferences: $tableReferences);
  }

  /**
   * Create the MySQL table reference builder.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return MySQLTableReference Returns the MySQL table reference builder.
   */
  protected function createTableReference(array|string $tableReferences): SQLTableReference
  {
    return new MySQLTableReference(query: $this->query, tableReferences: $tableReferences);
  }
}
