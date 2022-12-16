<?php

namespace Assegai\Orm\Metadata;

use Assegai\Orm\Enumerations\TableType;
use Closure;

/**
 * Arguments for TableMetadata class. Help to construct a TableMetadata object.
 */
class TableMetadataArgs
{
  /**
   * Constructs a TableMetadataArgs object.
   *
   * @param Closure|string $target The class to which the table is applied. The Closure target is a table in the class. A string target is a table defined in a JSON schema.
   * @param string|null $name Table's name. If name is not set then table's name will be generated from target's name.
   * @param TableType $type Table type. Tables can be regular, view, junction, etc.
   * @param Closure|string|null $orderBy Specifies a default order by used for queries from this table when no explicit order by is specified.
   * @param string|null $engine Table's database engine type (like "InnoDB", "MyISAM", etc).
   * @param string|null $database Database name. Used in MySQL and Sql Server.
   * @param string|null $schema Schema name. Used in Postgres and Sql Server.
   * @param bool|null $synchronize Indicates if schema synchronization is enabled or disabled for this entity. If it will be set to false then schema sync will and migrations ignore this entity. By default schema synchronization is enabled for all entities.
   * @param Closure|string|null $expression View expression.
   * @param array|null $dependsOn View dependencies.
   * @param bool|null $materialized Indicates if view is materialized
   * @param bool|null $withoutRowId If set to 'true' this option disables Sqlite's default behaviour of secretly creating an integer primary key column named 'rowId' on table creation.
   */
  public function __construct(
    public readonly Closure|string $target,
    public readonly ?string $name = null,
    public readonly TableType $type = TableType::REGULAR,
    public readonly Closure|string|null $orderBy = null,
    public readonly ?string $engine = null,
    public readonly ?string $database = null,
    public readonly ?string $schema = null,
    public readonly ?bool $synchronize = null,
    public readonly Closure|string|null $expression = null,
    public readonly ?array $dependsOn = null,
    public readonly ?bool $materialized = null,
    public readonly ?bool $withoutRowId = null,
  )
  {}
}