<?php

namespace Assegai\Orm\Queries\DDL;

use Assegai\Orm\Queries\Sql\SQLColumnDefinition;
use Stringable;

readonly class DDLDropStatement implements Stringable
{
  /**
   * Constructs the DDLDropStatement
   *
   * @param string $columnName
   * @param SQLColumnDefinition|null $columnDefinition
   */
  public function __construct(
    public string $columnName,
    public ?SQLColumnDefinition $columnDefinition = null
  )
  {
  }

  /**
   * @return string
   */
  public function __toString(): string
  {
    $columnName = $this?->columnDefinition->name ?? $this->columnName;
    return "DROP COLUMN `$columnName`";
  }
}