<?php

namespace Assegai\Orm\Queries\MsSql;

use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Queries\Sql\SQLKeyPart;

/**
 * MSSQL-specific ORDER BY key-part builder.
 */
class MsSqlKeyPart extends SQLKeyPart
{
  /**
   * Create a SQL Server key-part builder.
   *
   * @param string $key The column or expression to render.
   * @param bool|null $ascending The sort direction to append, or null to omit it.
   */
  public function __construct(string $key, ?bool $ascending = null)
  {
    parent::__construct(key: $key, ascending: $ascending, dialect: SQLDialect::MSSQL);
  }
}
