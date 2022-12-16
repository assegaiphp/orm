<?php

namespace Assegai\Orm\Attributes\Relations;

use Attribute;

/**
 * The JoinColumn attribute used on one-to-one relations to specify the owner side of a relationship.
 * It also can be used on both one-to-one and many-to-one relations to specify a custom column name
 * or a custom referenced column.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class JoinColumn
{
  public ?string $effectiveColumnName;
  public ?string $effectiveReferencedColumnName;
  public ?string $effectiveForeignKeyConstraintName;
  /**
   * @param string|null $name Name of the column.
   * @param string|null $referencedColumnName Name of the column in the entity to which this column is referenced.
   * @param string|null $foreignKeyConstraintName Name of the foreign key constraint.
   */
  public function __construct(
    public readonly ?string $name = null,
    public readonly ?string $referencedColumnName = null,
    public readonly ?string $foreignKeyConstraintName = null,
  )
  {
    $this->effectiveColumnName = $this->name;
    $this->effectiveReferencedColumnName = $this->referencedColumnName;
    $this->effectiveForeignKeyConstraintName = $this->foreignKeyConstraintName;
  }
}