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
   * Begin defining the table columns using MSSQL-specific fluent builders.
   *
   * @param array $columnDefinitions The column definitions to render.
   * @return MsSqlTableOptions Returns the MSSQL table-options builder.
   */
  public function columns(array $columnDefinitions): MsSqlTableOptions
  {
    return parent::columns($columnDefinitions);
  }

  /**
   * Create the MSSQL table-options builder.
   *
   * @param array $columnDefinitions The column definitions to render.
   * @return SQLTableOptions Returns the MSSQL table-options builder.
   */
  protected function createTableOptions(array $columnDefinitions): SQLTableOptions
  {
    return new MsSqlTableOptions(query: $this->query, columnDefinitions: $columnDefinitions);
  }
}
