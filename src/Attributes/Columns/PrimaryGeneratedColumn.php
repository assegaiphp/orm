<?php

namespace Assegai\Orm\Attributes\Columns;

use Assegai\Orm\Queries\Sql\ColumnType;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class PrimaryGeneratedColumn extends Column
{
  public function __construct(
    public string $name = 'id',
    public string $alias = '',
    public string $comment = ''
  )
  {
    parent::__construct(
      name: $name,
      alias: $alias,
      type: ColumnType::BIGINT_UNSIGNED,
      nullable: false,
      unsigned: true,
      autoIncrement: true,
      isPrimaryKey: true,
      comment: $comment
    );
  }
}
