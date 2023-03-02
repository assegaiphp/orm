<?php

namespace Assegai\Orm\Queries\DDL;

use Assegai\Orm\Queries\Sql\SQLColumnDefinition;
use Stringable;

/**
 * Class DDLAddStatement
 * This class represents a data definition language (DDL) add statement used to add a new column to a table.
 */
readonly class DDLAddStatement implements Stringable
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

  public function __toString(): string
  {
    return "ADD COLUMN $this->columnDefinition";
  }
}