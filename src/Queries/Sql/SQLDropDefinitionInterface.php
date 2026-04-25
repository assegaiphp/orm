<?php

namespace Assegai\Orm\Queries\Sql;

/**
 * Describes the minimum DROP capability shared across SQL-family builders.
 */
interface SQLDropDefinitionInterface
{
  /**
   * Begin a DROP TABLE statement.
   *
   * @param string $tableName The table name to drop.
   * @return SQLDropTableStatement Returns the DROP TABLE statement builder.
   */
  public function table(string $tableName): SQLDropTableStatement;
}
