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
  public function __construct(
    public SQLColumnDefinition $columnDefinition,
    public string $columnName
  )
  {}

  public function __toString(): string
  {
    return "ADD COLUMN $this->columnDefinition";
  }
}