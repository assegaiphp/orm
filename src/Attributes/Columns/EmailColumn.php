<?php

namespace Assegai\Orm\Attributes\Columns;

use Assegai\Orm\Queries\Sql\SQLDataTypes;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class EmailColumn extends Column
{
  public function __construct(
    public string $name = 'email',
    public string $alias = '',
    public string $comment = '',
  )
  {
    parent::__construct(
      name: $name,
      alias: $alias,
      type: SQLDataTypes::VARCHAR,
      lengthOrValues: 60
    );
  }
}