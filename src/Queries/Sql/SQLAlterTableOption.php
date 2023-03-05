<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Traits\ExecutableTrait;

/**
 * The SQLAlterTableOption class provides methods for altering a table in a SQL database.
 */
final class SQLAlterTableOption
{
  use ExecutableTrait;

  /**
   * Constructs an instance of the SQLAlterTableOption.
   *
   * @param SQLQuery $query The SQLQuery instance used to construct queries.
   */
  public function __construct(private readonly SQLQuery $query)
  {
  }

  /**
   * Adds a new column to the table.
   *
   * @param SQLColumnDefinition $dataType The definition of the new column.
   * @param bool|null $first Specifies that the new column should be positioned first within a table row.
   * @param string|null $afterColumn Specifies the column after which to position the new column within a table row.
   * @return SQLAlterTableOption
   */
  public function addColumn(SQLColumnDefinition $dataType, ?bool $first = false, ?string $afterColumn = null): SQLAlterTableOption
  {
    $this->query->appendQueryString(tail: "ADD $dataType");
    if ($first)
    {
      $this->query->appendQueryString(tail: "FIRST");
    }
    else if (!is_null($afterColumn))
    {
      $this->query->appendQueryString(tail: "AFTER `$afterColumn`");
    }
    return $this;
  }

  /**
   * Modifies an existing column in the table.
   *
   * @param SQLColumnDefinition $dataType The new definition of the column.
   * @return SQLAlterTableOption
   */
  public function modifyColumn(SQLColumnDefinition $dataType): SQLAlterTableOption
  {
    $this->query->appendQueryString(tail: "MODIFY COLUMN $dataType");
    return $this;
  }

  /**
   * Renames an existing column in the table.
   *
   * @param string $oldColumnName The name of the column to be renamed.
   * @param string $newColumnName The new name for the column.
   * @return SQLAlterTableOption
   */
  public function renameColumn(string $oldColumnName, string $newColumnName): SQLAlterTableOption
  {
    $this->query->appendQueryString(tail: "RENAME COLUMN `$oldColumnName` TO `$newColumnName`");
    return $this;
  }

  /**
   * Drops a column from the table.
   *
   * @param string $columnName The name of the column to be dropped.
   * @return SQLAlterTableOption
   */
  public function dropColumn(string $columnName): SQLAlterTableOption
  {
    $this->query->appendQueryString(tail: "DROP COLUMN `$columnName`");
    return $this;
  }
}
