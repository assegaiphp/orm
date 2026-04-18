<?php

namespace Assegai\Orm\Queries\MsSql;

use Assegai\Orm\Queries\Sql\SQLCreateDatabaseStatement;

/**
 * MSSQL-specific CREATE DATABASE statement builder.
 */
class MsSqlCreateDatabaseStatement extends SQLCreateDatabaseStatement
{
  /**
   * Build the CREATE DATABASE statement for SQL Server.
   *
   * @return string Returns the rendered SQL Server CREATE DATABASE statement.
   */
  protected function buildQueryString(): string
  {
    $statement = 'CREATE DATABASE ' . $this->buildDatabaseName();

    if (!$this->checkIfNotExists) {
      return $statement;
    }

    return "IF DB_ID(N'{$this->escapeLiteral($this->dbName)}') IS NULL $statement";
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
