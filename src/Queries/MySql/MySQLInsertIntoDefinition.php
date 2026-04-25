<?php

namespace Assegai\Orm\Queries\MySql;

use Assegai\Orm\Queries\Sql\SQLInsertIntoDefinition;
use Assegai\Orm\Queries\Sql\SQLInsertIntoMultipleStatement;
use Assegai\Orm\Queries\Sql\SQLInsertIntoStatement;

/**
 * MySQL-specific INSERT entry point.
 */
class MySQLInsertIntoDefinition extends SQLInsertIntoDefinition
{
  /**
   * Begins a MySQL single-row INSERT statement.
   *
   * @param array<int|string, string> $columns The target columns for the insert.
   * @return MySQLInsertIntoStatement Returns the MySQL single-row insert builder.
   */
  public function singleRow(array $columns = []): MySQLInsertIntoStatement
  {
    return $this->createSingleRowStatement(columns: $columns);
  }

  /**
   * Begins a MySQL multi-row INSERT statement.
   *
   * @param array<int|string, string> $columns The target columns for the insert.
   * @return MySQLInsertIntoMultipleStatement Returns the MySQL multi-row insert builder.
   */
  public function multipleRows(array $columns = []): MySQLInsertIntoMultipleStatement
  {
    return $this->createMultipleRowsStatement(columns: $columns);
  }

  /**
   * Creates the MySQL single-row INSERT builder.
   *
   * @param array<int|string, string> $columns The target columns for the insert.
   * @return MySQLInsertIntoStatement Returns the MySQL single-row insert builder.
   */
  protected function createSingleRowStatement(array $columns = []): SQLInsertIntoStatement
  {
    return new MySQLInsertIntoStatement(
      query: $this->query,
      columns: $columns,
    );
  }

  /**
   * Creates the MySQL multi-row INSERT builder.
   *
   * @param array<int|string, string> $columns The target columns for the insert.
   * @return MySQLInsertIntoMultipleStatement Returns the MySQL multi-row insert builder.
   */
  protected function createMultipleRowsStatement(array $columns = []): SQLInsertIntoMultipleStatement
  {
    return new MySQLInsertIntoMultipleStatement(
      query: $this->query,
      columns: $columns,
    );
  }
}
