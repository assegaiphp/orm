<?php

namespace Assegai\Orm\Attributes\Relations;

use Attribute;

/**
 * The JoinTable attribute is used in many-to-many relationship to specify owner side of relationship.
 * It's also used to set a custom junction table's name, column names and referenced columns.
 */
#[Attribute(Attribute::TARGET_PROPERTY|Attribute::TARGET_PARAMETER)]
class JoinTable
{
  /**
   * @param string|null $name Name of the table that will be created to store values of the both tables (join table).
   * By default, is auto generated.
   * @param string|null $joinColumn First column of the join table.
   * @param string|null $inverseJoinColumn Second (inverse) column of the join table.
   * @param string|null $database Database where join table will be created.
   * Works only in some databases (like mysql and mssql).
   * @param string|null $schema Schema where join table will be created.
   * Works only in some databases (like postgres and mssql).
   * @param bool|null $synchronize Indicates if schema synchronization is enabled or disabled junction table.
   * If it will be set to false then schema sync will and migrations ignores junction table.
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