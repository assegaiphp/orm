<?php

namespace Assegai\Orm\Relations;

class JoinColumnOptions
{
  /**
   * Constructs the `JoinColumnOptions`
   *
   * @param null|string $name Name of the column.
   * @param string|null $referenceColumn Name of the column in the entity to which this column is referenced.
   */
  public function __construct(
    public ?string $name = null,
    public ?string $referenceColumn = null,
  )
  {
  }
}