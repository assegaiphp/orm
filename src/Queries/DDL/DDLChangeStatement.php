<?php

namespace Assegai\Orm\Queries\DDL;

use Assegai\Orm\Queries\Sql\SQLColumnDefinition;
use Stringable;

readonly class DDLChangeStatement implements Stringable
{
  /**
   * @param SQLColumnDefinition $columnDefinition
   */
  public function __construct(
    public SQLColumnDefinition $columnDefinition,
    public string $columnName
  )
  {}

  /**
   * @return string
   */
  public function __toString(): string
  {
    return "CHANGE COLUMN $this->columnDefinition";
  }
}