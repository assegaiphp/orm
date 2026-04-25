<?php

namespace Assegai\Orm\Queries\MsSql;

use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Queries\Sql\SQLRenameTableStatement;
use Assegai\Orm\Util\SqlDialectHelper;

/**
 * MSSQL-specific table rename statement.
 */
class MsSqlRenameTableStatement extends SQLRenameTableStatement
{
  /**
   * Build the SQL Server table rename statement.
   *
   * @return string Returns the rendered SQL Server rename statement.
   */
  protected function buildQueryString(): string
  {
    $oldName = $this->escapeLiteral(SqlDialectHelper::quoteCompositeIdentifier($this->oldTableName, SQLDialect::MSSQL));
    $newName = $this->escapeLiteral(SqlDialectHelper::unqualifyIdentifier($this->newTableName));

    return "EXEC sp_rename N'{$oldName}', N'{$newName}', N'OBJECT'";
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
