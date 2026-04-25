<?php

namespace Assegai\Orm\Queries\MsSql;

use Assegai\Orm\Queries\Sql\SQLSelectExpression;
use Assegai\Orm\Queries\Sql\SQLTableReference;

/**
 * MSSQL-specific SELECT expression builder.
 */
class MsSqlSelectExpression extends SQLSelectExpression
{
  /**
   * Add the FROM clause and keep the fluent chain on the MSSQL builder path.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return MsSqlTableReference Returns the MSSQL table reference builder.
   */
  public function from(array|string $tableReferences): MsSqlTableReference
  {
    return $this->createTableReference(tableReferences: $tableReferences);
  }

  /**
   * Create the MSSQL table reference builder.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return SQLTableReference Returns the MSSQL table reference builder.
   */
  protected function createTableReference(array|string $tableReferences): SQLTableReference
  {
    return new MsSqlTableReference(query: $this->query, tableReferences: $tableReferences);
  }
}
