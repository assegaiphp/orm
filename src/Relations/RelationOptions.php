<?php

namespace Assegai\Orm\Relations;

use Assegai\Orm\Enumerations\CascadeOption;
use Assegai\Orm\Enumerations\DeferrableType;
use Assegai\Orm\Enumerations\OnDeleteType;
use Assegai\Orm\Enumerations\OnUpdateType;
use Assegai\Orm\Enumerations\OrphanedRowAction;

/**
 * Defines the options for a relation.
 *
 * @package Assegai\Orm\Relations
 */
readonly class RelationOptions
{
  /**
   * @param null|bool|CascadeOption|array $cascade Sets cascades options for the given relation.
   * If set to true then it means that related object can be allowed to be inserted or updated in the database.
   * You can separately restrict cascades to insertion or updates using following syntax:
   *
   * cascade: ["insert", "update", "remove", "soft-remove", "recover"] // include or exclude one of them
   *
   * @param null|bool $isNullable Indicates if relation column value can be nullable or not.
   *
   * @param null|OnDeleteType $onDelete Database cascade action on delete.
   *
   * @param null|OnUpdateType $onUpdate Database cascade action on update.
   *
   * @param null|DeferrableType $deferrable Indicate if foreign key constraints can be deferred.
   *
   * @param null|bool $isPrimary Indicates if this relation will be a primary key.
   * Can be used only for many-to-one and owner one-to-one relations.
   *
   * @param null|bool $createForeignKeyConstraints Indicates whether foreign key constraints will be created for join columns.
   * Can be used only for many-to-one and owner one-to-one relations.
   * Defaults to true.
   *
   * @param null|bool $isEager Set this relation to be eager.
   * Eager relations are always loaded automatically when relation's owner entity is loaded using find* methods.
   * Only using QueryBuilder prevents loading eager relations.
   * Eager flag cannot be set from both sides of relation - you can eager load only one side of the relationship.
   *
   * @param null|bool $isPersistent Indicates if persistence is enabled for the relation.
   * By default, its enabled, but if you want to avoid any changes in the relation to be reflected in the database
   * you can disable it.
   * If its disabled you can only change a relation from inverse side of a relation or using relation query builder
   * functionality.
   * This is useful for performance optimization since its disabling avoid multiple extra queries during entity save.
   *
   * @param null|OrphanedRowAction $orphanedRowAction  When a child row is removed from its parent, determines if
   * the child row should be orphaned (default) or deleted.
   */
  public function __construct(
    public null|bool|CascadeOption|array  $cascade = null,
    public ?bool                          $isNullable = null,
    public ?OnDeleteType                  $onDelete = null,
    public ?OnUpdateType                  $onUpdate = null,
    public ?DeferrableType                $deferrable = null,
    public ?bool                          $isPrimary = null,
    public ?bool                          $createForeignKeyConstraints = null,
    public ?bool                          $isEager = null,
    public ?bool                          $isPersistent = null,
    public ?OrphanedRowAction             $orphanedRowAction = null,
    public array                          $exclude = ['password']
  ) { }
}