<?php

namespace Assegai\Orm\Queries\PostgreSql;

use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Queries\Sql\SQLKeyPart;

/**
 * PostgreSQL-owned sort-key builder.
 */
final class PostgreSQLKeyPart extends SQLKeyPart
{
  /**
   * Creates a PostgreSQL sort-key builder.
   *
   * @param string $key The identifier to render.
   * @param bool|null $ascending The sort direction to append, or null to omit it.
   *
   * @throws \InvalidArgumentException Thrown when the identifier is unsafe.
   */
  public function __construct(string $key, ?bool $ascending = null)
  {
    parent::__construct(key: $key, ascending: $ascending, dialect: SQLDialect::POSTGRESQL);
  }
}
