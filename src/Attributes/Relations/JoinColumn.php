<?php

namespace Assegai\Orm\Attributes\Relations;

use Assegai\Orm\Queries\Sql\ColumnType;
use Attribute;

/**
 * The JoinColumn attribute used on one-to-one relations to specify the owner side of a relationship.
 * It also can be used on both one-to-one and many-to-one relations to specify a custom column name
 * or a custom referenced column.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class JoinColumn
{
  /**
   * @var string|null The effective column name.
   */
  public ?string $effectiveColumnName;
  /**
   * @var string|null The effective referenced column name.
   */
  public ?string $effectiveReferencedColumnName;
  /**
   * @var string|null The effective foreign key constraint name.
   */
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
    public readonly ColumnType $type = ColumnType::BIGINT_UNSIGNED,
  )
  {
    $this->effectiveColumnName = $this->name;
    $this->effectiveReferencedColumnName = $this->referencedColumnName;
    $this->effectiveForeignKeyConstraintName = $this->foreignKeyConstraintName;
  }
}