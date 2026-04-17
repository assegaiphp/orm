<?php

namespace Assegai\Orm\Queries\SQLite;

use Assegai\Orm\Queries\Sql\SQLAlterDefinition;

/**
 * SQLite-specific ALTER builder.
 */
class SQLiteAlterDefinition extends SQLAlterDefinition
{
  /**
   * Begin an ALTER TABLE statement using the SQLite-specific table option builder.
   *
   * @param string $tableName The table being altered.
   * @return SQLiteAlterTableOption Returns the SQLite alter-table option builder.
   */
  public function table(string $tableName): SQLiteAlterTableOption
  {
    return parent::table($tableName);
  }

  /**
   * Create the alter-table option builder for this dialect.
   *
   * @return SQLiteAlterTableOption Returns the SQLite alter-table option builder.
   */
  protected function createAlterTableOption(): SQLiteAlterTableOption
  {
    return new SQLiteAlterTableOption(query: $this->query);
  }
}
