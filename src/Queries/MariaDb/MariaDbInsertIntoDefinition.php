<?php

namespace Assegai\Orm\Queries\MariaDb;

use Assegai\Orm\Queries\MySql\MySQLInsertIntoDefinition;

/**
 * MariaDB-specific INSERT entry point.
 */
class MariaDbInsertIntoDefinition extends MySQLInsertIntoDefinition
{
  /**
   * Begin a MariaDB single-row INSERT statement.
   *
   * @param array<int|string, string> $columns The target columns for the insert.
   * @return MariaDbInsertIntoStatement Returns the MariaDB single-row insert builder.
   */
  public function singleRow(array $columns = []): MariaDbInsertIntoStatement
  {
    return new MariaDbInsertIntoStatement(
      query: $this->query,
      columns: $columns
    );
  }

  /**
   * Begin a MariaDB multi-row INSERT statement.
   *
   * @param array<int|string, string> $columns The target columns for the insert.
   * @return MariaDbInsertIntoMultipleStatement Returns the MariaDB multi-row insert builder.
   */
  public function multipleRows(array $columns = []): MariaDbInsertIntoMultipleStatement
  {
    return new MariaDbInsertIntoMultipleStatement(
      query: $this->query,
      columns: $columns
    );
  }
}
