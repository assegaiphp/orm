<?php

namespace Assegai\Orm\Queries\DDL;

use Assegai\Orm\Queries\Sql\SQLColumnDefinition;
use Stringable;

readonly class DDLDropStatement implements Stringable
{
  /**
   * @param SQLColumnDefinition $columnDefinition
   * @param string $columnName
   */
  public function __construct(
    public SQLColumnDefinition $columnDefinition,
    public string $columnName
  )
  {
  }

  /**
   * @return string
   */
  public function __toString(): string
  {
    $columnName = $this->columnDefinition->name ?? $this->columnName;
    return "DROP COLUMN $columnName";
  }
}