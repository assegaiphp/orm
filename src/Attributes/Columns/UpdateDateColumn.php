<?php

namespace Assegai\Orm\Attributes\Columns;

use Assegai\Orm\Queries\Sql\DataType;
use Attribute;

/**
 * `UpdateDateColumn` is a special column that is automatically set to the entity's update time each time you call save of entity manager or repository. You don't need to set this column - it will be automatically set.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class UpdateDateColumn extends Column
{
  public function __construct(
    public string $name = 'updated_at',
    public string $alias = 'updatedAt',
    public string $comment = '',
  )
  {
    parent::__construct(
      name: $name,
      alias: $alias,
      type: DataType::DATETIME,
      allowNull: false,
      defaultValue: 'CURRENT_TIMESTAMP',
      onUpdate: 'CURRENT_TIMESTAMP',
      comment: $comment
    );
  }
}
