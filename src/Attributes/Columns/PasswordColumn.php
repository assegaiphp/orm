<?php

namespace Assegai\Orm\Attributes\Columns;

use Assegai\Orm\Queries\Sql\ColumnType;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class PasswordColumn extends Column
{
  public function __construct(
    public string $name = 'password',
    public string $alias = '',
    public string $comment = '',
  )
  {
    parent::__construct(
      name: $name,
      alias: $alias,
      type: ColumnType::TEXT,
      nullable: false,
      default: null,
      comment: $comment
    );
  }
}
