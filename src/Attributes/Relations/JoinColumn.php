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
   * @var string|null Internal resolved column name after defaults are applied.
   */
  public ?string $effectiveColumnName;
  /**
   * @var string|null Internal resolved target column name after defaults are applied.
   */
  public ?string $effectiveReferencedColumnName;
  /**
   * @var string|null Internal resolved foreign key constraint name after defaults are applied.
   */
  public ?string $effectiveForeignKeyConstraintName;

  /**
   * @param string|null $name Name of the foreign key column on the current table.
   * Example: in `restaurants.organization_id`, the join column name is `'organization_id'`.
   * @param string|null $referencedColumnName Name of the target column on the related table.
   * In plain terms: "which column on the other table are we matching against?"
   * Most of the time this is just `'id'`.
   * Example: `#[JoinColumn(name: 'organization_id', referencedColumnName: 'id')]`
   * @param string|null $foreignKeyConstraintName Name of the foreign key constraint.
   * @param ColumnType $type SQL column type for the foreign key column.
   */
  public function __construct(
    public readonly ?string $name = null,
    public readonly ?string $referencedColumnName = null,
    public readonly ?string $foreignKeyConstraintName = null,
    public readonly ColumnType $type = ColumnType::BIGINT_UNSIGNED,
  )
  {
    $this->effectiveColumnName = $this->name;
    $this->effectiveReferencedColumnName = $this->referencedColumnName ?? 'id';
    $this->effectiveForeignKeyConstraintName = $this->foreignKeyConstraintName;
  }
}
