<?php

namespace Assegai\Orm\Queries\MsSql;

use Assegai\Orm\Queries\Sql\SQLAlterDefinition;
use Assegai\Orm\Queries\Sql\SQLAlterTableOption;

/**
 * MSSQL-specific ALTER entry point.
 */
class MsSqlAlterDefinition extends SQLAlterDefinition
{
  /**
   * Begin an ALTER TABLE statement using MSSQL-specific fluent builders.
   *
   * @param string $tableName The table being altered.
   * @return MsSqlAlterTableOption Returns the MSSQL alter-table builder.
   */
  public function table(string $tableName): MsSqlAlterTableOption
  {
    return parent::table($tableName);
  }

  /**
   * Create the MSSQL alter-table option builder.
   *
   * @return SQLAlterTableOption Returns the MSSQL alter-table builder.
   */
  protected function createAlterTableOption(): SQLAlterTableOption
  {
    return new MsSqlAlterTableOption(query: $this->query);
  }
}
