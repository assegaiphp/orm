<?php

namespace Assegai\Orm\Queries\MariaDb;

use Assegai\Orm\Queries\MySql\MySQLInsertIntoDefinition;
use Assegai\Orm\Queries\Sql\SQLInsertIntoMultipleStatement;
use Assegai\Orm\Queries\Sql\SQLInsertIntoStatement;

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
    return $this->createSingleRowStatement(columns: $columns);
  }

  /**
   * Begin a MariaDB multi-row INSERT statement.
   *
   * @param array<int|string, string> $columns The target columns for the insert.
   * @return MariaDbInsertIntoMultipleStatement Returns the MariaDB multi-row insert builder.
   */
  public function multipleRows(array $columns = []): MariaDbInsertIntoMultipleStatement
  {
    return $this->createMultipleRowsStatement(columns: $columns);
  }

  /**
   * Creates the MariaDB single-row INSERT builder.
   *
   * @param array<int|string, string> $columns The target columns for the insert.
   * @return MariaDbInsertIntoStatement Returns the MariaDB single-row insert builder.
   */
  protected function createSingleRowStatement(array $columns = []): SQLInsertIntoStatement
  {
    return new MariaDbInsertIntoStatement(
      query: $this->query,
      columns: $columns,
    );
  }

  /**
   * Creates the MariaDB multi-row INSERT builder.
   *
   * @param array<int|string, string> $columns The target columns for the insert.
   * @return MariaDbInsertIntoMultipleStatement Returns the MariaDB multi-row insert builder.
   */
  protected function createMultipleRowsStatement(array $columns = []): SQLInsertIntoMultipleStatement
  {
    return new MariaDbInsertIntoMultipleStatement(
      query: $this->query,
      columns: $columns,
    );
  }
}
