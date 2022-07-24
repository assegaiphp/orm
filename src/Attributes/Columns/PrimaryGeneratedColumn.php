<?php

namespace Assegai\Orm\Attributes\Columns;

use Assegai\Orm\Queries\Sql\SQLDataTypes;
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
      type: SQLDataTypes::BIGINT_UNSIGNED,
      allowNull: false,
      signed: false,
      autoIncrement: true,
      isPrimaryKey: true,
      comment: $comment
    );
  }
}
