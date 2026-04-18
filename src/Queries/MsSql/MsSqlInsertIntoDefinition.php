<?php

namespace Assegai\Orm\Queries\MsSql;

use Assegai\Orm\Queries\Sql\SQLInsertIntoDefinition;
use Assegai\Orm\Queries\Sql\SQLInsertIntoMultipleStatement;
use Assegai\Orm\Queries\Sql\SQLInsertIntoStatement;

/**
 * MSSQL-specific INSERT entry point.
 */
class MsSqlInsertIntoDefinition extends SQLInsertIntoDefinition
{
  /**
   * Begin a SQL Server single-row INSERT statement.
   *
   * @param array<int|string, string> $columns The target columns for the insert.
   * @return MsSqlInsertIntoStatement Returns the MSSQL single-row insert builder.
   */
  public function singleRow(array $columns = []): MsSqlInsertIntoStatement
  {
    return $this->createSingleRowStatement(columns: $columns);
  }

  /**
   * Begin a SQL Server multi-row INSERT statement.
   *
   * @param array<int|string, string> $columns The target columns for the insert.
   * @return MsSqlInsertIntoMultipleStatement Returns the MSSQL multi-row insert builder.
   */
  public function multipleRows(array $columns = []): MsSqlInsertIntoMultipleStatement
  {
    return $this->createMultipleRowsStatement(columns: $columns);
  }

  /**
   * Create the MSSQL single-row INSERT builder.
   *
   * @param array<int|string, string> $columns The target columns for the insert.
   * @return SQLInsertIntoStatement Returns the MSSQL single-row insert builder.
   */
  protected function createSingleRowStatement(array $columns = []): SQLInsertIntoStatement
  {
    return new MsSqlInsertIntoStatement(query: $this->query, columns: $columns);
  }

  /**
   * Create the MSSQL multi-row INSERT builder.
   *
   * @param array<int|string, string> $columns The target columns for the insert.
   * @return SQLInsertIntoMultipleStatement Returns the MSSQL multi-row insert builder.
   */
  protected function createMultipleRowsStatement(array $columns = []): SQLInsertIntoMultipleStatement
  {
    return new MsSqlInsertIntoMultipleStatement(query: $this->query, columns: $columns);
  }
}
