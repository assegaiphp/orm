<?php

namespace Assegai\Orm\Attributes\Relations;

use Attribute;

/**
 * The JoinTable attribute is used in many-to-many relationship to specify owner side of relationship.
 * It's also used to set a custom junction table's name, column names and referenced columns.
 *
 * Example:
 * ```php
 * #[JoinTable(
 *   name: 'organization_users',
 *   joinColumn: 'organization_id',
 *   inverseJoinColumn: 'user_id'
 * )]
 * ```
 */
#[Attribute(Attribute::TARGET_PROPERTY|Attribute::TARGET_PARAMETER)]
class JoinTable
{
  /**
   * @param string|null $name Name of the table that will be created to store values of the both tables (join table).
   * By default, it is auto generated. Example: `'organization_users'`.
   * @param string|null $joinColumn The column in the junction table that points back to the current entity.
   * Example: on `OrganizationEntity::$users`, this is usually `'organization_id'`.
   * @param string|null $inverseJoinColumn The column in the junction table that points to the related entity.
   * Example: on `OrganizationEntity::$users`, this is usually `'user_id'`.
   * @param string|null $database Database where join table will be created.
   * Works only in some databases, such as MySQL and MSSQL.
   * @param string|null $schema Schema where join table will be created.
   * Works only in some databases, such as PostgreSQL and MSSQL.
   * @param bool|null $synchronize Indicates if schema synchronization is enabled or disabled for the junction table.
   * If it will be set to false then schema sync and migrations ignore the junction table.
   * By default, schema synchronization is enabled.
   */
  public function __construct(
    public readonly ?string $name = null,
    public readonly ?string $joinColumn = null,
    public readonly ?string $inverseJoinColumn = null,
    public readonly ?string $database = null,
    public readonly ?string $schema = null,
    public readonly ?bool $synchronize = null,
  )
  {}
}
