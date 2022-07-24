<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Traits\ExecutableTrait;

final class SQLAlterTableOption
{
  use ExecutableTrait;

  public function __construct(
    private SQLQuery $query
  )
  {
  }

  /**
   * @param SQLColumnDefinition $dataType
   * @param bool $first Specifies that the new column should be positioned first within a table row.
   * @param null|string $afterColumn Specifies the column after which to position the new column within a table row.
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
   * @param SQLColumnDefinition $dataType
   * @return $this
   */
  public function modifyColumn(SQLColumnDefinition $dataType): SQLAlterTableOption
  {
    $this->query->appendQueryString(tail: "MODIFY COLUMN $dataType");
    return $this;
  }

  /**
   * @param string $oldColumnName
   * @param string $newColumnName
   * @return $this
   */
  public function renameColumn(string $oldColumnName, string $newColumnName): SQLAlterTableOption
  {
    $this->query->appendQueryString(tail: "RENAME COLUMN `$oldColumnName` TO `$newColumnName`");
    return $this;
  }

  /**
   * @param string $columnName
   * @return $this
   */
  public function dropColumn(string $columnName): SQLAlterTableOption
  {
    $this->query->appendQueryString(tail: "DROP COLUMN `$columnName`");
    return $this;
  }
}
