<?php

namespace Assegai\Orm\Queries\SQLite;

use Assegai\Orm\Queries\Sql\SQLInsertIntoDefinition;
use Assegai\Orm\Queries\Sql\SQLInsertIntoMultipleStatement;
use Assegai\Orm\Queries\Sql\SQLInsertIntoStatement;

/**
 * SQLite-specific INSERT entry point.
 */
class SQLiteInsertIntoDefinition extends SQLInsertIntoDefinition
{
  /**
   * Begins a SQLite single-row INSERT statement.
   *
   * @param array<int|string, string> $columns The target columns for the insert.
   * @return SQLiteInsertIntoStatement Returns the SQLite single-row insert builder.
   */
  public function singleRow(array $columns = []): SQLiteInsertIntoStatement
  {
    return $this->createSingleRowStatement(columns: $columns);
  }

  /**
   * Begins a SQLite multi-row INSERT statement.
   *
   * @param array<int|string, string> $columns The target columns for the insert.
   * @return SQLiteInsertIntoMultipleStatement Returns the SQLite multi-row insert builder.
   */
  public function multipleRows(array $columns = []): SQLiteInsertIntoMultipleStatement
  {
    return $this->createMultipleRowsStatement(columns: $columns);
  }

  /**
   * Creates the SQLite single-row INSERT builder.
   *
   * @param array<int|string, string> $columns The target columns for the insert.
   * @return SQLiteInsertIntoStatement Returns the SQLite single-row insert builder.
   */
  protected function createSingleRowStatement(array $columns = []): SQLInsertIntoStatement
  {
    return new SQLiteInsertIntoStatement(
      query: $this->query,
      columns: $columns,
    );
  }

  /**
   * Creates the SQLite multi-row INSERT builder.
   *
   * @param array<int|string, string> $columns The target columns for the insert.
   * @return SQLiteInsertIntoMultipleStatement Returns the SQLite multi-row insert builder.
   */
  protected function createMultipleRowsStatement(array $columns = []): SQLInsertIntoMultipleStatement
  {
    return new SQLiteInsertIntoMultipleStatement(
      query: $this->query,
      columns: $columns,
    );
  }
}
