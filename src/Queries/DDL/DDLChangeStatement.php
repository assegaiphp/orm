<?php

namespace Assegai\Orm\Queries\DDL;

use Assegai\Orm\Queries\Sql\SQLColumnDefinition;
use Stringable;

readonly class DDLChangeStatement implements Stringable
{
  /**
   * @param string $columnName
   * @param SQLColumnDefinition $columnDefinition
   */
  public function __construct(
    public string $columnName,
    public SQLColumnDefinition $columnDefinition,
  )
  {}

  /**
   * @return string
   */
  public function __toString(): string
  {
    if ($this->columnDefinition->name)
    {
      return "CHANGE COLUMN $this->columnDefinition";
    }

    $columnName = $this->columnDefinition->name;

    if (empty($columnName))
    {
      $columnName = $this->columnName;
    }

    return "CHANGE COLUMN `$columnName` $this->columnDefinition";
  }
}