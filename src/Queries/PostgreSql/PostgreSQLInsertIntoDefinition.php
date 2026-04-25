<?php

namespace Assegai\Orm\Queries\PostgreSql;

use Assegai\Orm\Queries\Sql\SQLInsertIntoDefinition;
use Assegai\Orm\Queries\Sql\SQLInsertIntoMultipleStatement;
use Assegai\Orm\Queries\Sql\SQLInsertIntoStatement;

/**
 * PostgreSQL-specific INSERT entry point.
 */
class PostgreSQLInsertIntoDefinition extends SQLInsertIntoDefinition
{
  /**
   * Begins a PostgreSQL single-row INSERT statement.
   *
   * @param array<int|string, string> $columns The target columns for the insert.
   * @return PostgreSQLInsertIntoStatement Returns the PostgreSQL single-row insert builder.
   */
  public function singleRow(array $columns = []): PostgreSQLInsertIntoStatement
  {
    return $this->createSingleRowStatement(columns: $columns);
  }

  /**
   * Begins a PostgreSQL multi-row INSERT statement.
   *
   * @param array<int|string, string> $columns The target columns for the insert.
   * @return PostgreSQLInsertIntoMultipleStatement Returns the PostgreSQL multi-row insert builder.
   */
  public function multipleRows(array $columns = []): PostgreSQLInsertIntoMultipleStatement
  {
    return $this->createMultipleRowsStatement(columns: $columns);
  }

  /**
   * Creates the PostgreSQL single-row INSERT builder.
   *
   * @param array<int|string, string> $columns The target columns for the insert.
   * @return PostgreSQLInsertIntoStatement Returns the PostgreSQL single-row insert builder.
   */
  protected function createSingleRowStatement(array $columns = []): SQLInsertIntoStatement
  {
    return new PostgreSQLInsertIntoStatement(
      query: $this->query,
      columns: $columns,
    );
  }

  /**
   * Creates the PostgreSQL multi-row INSERT builder.
   *
   * @param array<int|string, string> $columns The target columns for the insert.
   * @return PostgreSQLInsertIntoMultipleStatement Returns the PostgreSQL multi-row insert builder.
   */
  protected function createMultipleRowsStatement(array $columns = []): SQLInsertIntoMultipleStatement
  {
    return new PostgreSQLInsertIntoMultipleStatement(
      query: $this->query,
      columns: $columns,
    );
  }
}
