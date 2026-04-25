<?php

namespace Assegai\Orm\Queries\MsSql;

use Assegai\Orm\Queries\Sql\SQLDropDatabaseStatement;

/**
 * MSSQL-specific DROP DATABASE statement builder.
 */
class MsSqlDropDatabaseStatement extends SQLDropDatabaseStatement
{
  /**
   * Build the DROP DATABASE statement for SQL Server.
   *
   * @return string Returns the rendered SQL Server DROP DATABASE statement.
   */
  protected function buildQueryString(): string
  {
    $statement = 'DROP DATABASE ' . $this->buildDatabaseName();

    if (!$this->checkIfExists) {
      return $statement;
    }

    return "IF DB_ID(N'{$this->escapeLiteral($this->dbName)}') IS NOT NULL $statement";
  }

  /**
   * Escape a SQL Server Unicode string literal.
   *
   * @param string $value The raw string literal value.
   * @return string Returns the escaped SQL Server literal.
   */
  private function escapeLiteral(string $value): string
  {
    return str_replace("'", "''", $value);
  }
}
