<?php

namespace Assegai\Orm\Queries\MsSql;

use Assegai\Orm\Queries\Sql\SQLCreateTableStatement;
use Assegai\Orm\Queries\Sql\SQLTableOptions;

/**
 * MSSQL-specific CREATE TABLE statement builder.
 */
class MsSqlCreateTableStatement extends SQLCreateTableStatement
{
  /**
   * Build the SQL Server CREATE TABLE statement.
   *
   * SQL Server does not support the generic TEMPORARY table prefix used by
   * other SQL-family builders, so this renderer emits a standard CREATE TABLE
   * statement and optionally guards it with an OBJECT_ID check.
   *
   * @return string Returns the rendered SQL Server CREATE TABLE statement.
   */
  protected function buildQueryString(): string
  {
    $createStatement = 'CREATE TABLE ' . $this->buildTableNameExpression();

    if (!$this->checkIfNotExists) {
      return $createStatement;
    }

    return "IF OBJECT_ID(N'{$this->buildObjectLookupName()}', N'U') IS NULL {$createStatement}";
  }

  /**
   * Begin defining the table columns using MSSQL-specific fluent builders.
   *
   * @param array $columns The column definitions to render.
   * @return MsSqlTableOptions Returns the MSSQL table-options builder.
   */
  public function columns(array $columns): MsSqlTableOptions
  {
    return parent::columns($columns);
  }

  /**
   * Create the MSSQL table-options builder.
   *
   * @param array $columns The column definitions to render.
   * @return SQLTableOptions Returns the MSSQL table-options builder.
   */
  protected function createTableOptions(array $columns): SQLTableOptions
  {
    return new MsSqlTableOptions(query: $this->query, columns: $columns);
  }

  /**
   * Build the OBJECT_ID lookup name used by the guarded CREATE TABLE statement.
   *
   * @return string Returns the quoted table name escaped for string-literal use.
   */
  protected function buildObjectLookupName(): string
  {
    return str_replace("'", "''", $this->buildTableNameExpression());
  }
}
