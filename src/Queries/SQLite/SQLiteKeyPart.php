<?php

namespace Assegai\Orm\Queries\SQLite;

use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Queries\Sql\SQLKeyPart;

/**
 * SQLite-owned sort-key builder.
 */
final class SQLiteKeyPart extends SQLKeyPart
{
  /**
   * Creates a SQLite sort-key builder.
   *
   * @param string $key The identifier to render.
   * @param bool|null $ascending The sort direction to append, or null to omit it.
   *
   * @throws \InvalidArgumentException Thrown when the identifier is unsafe.
   */
  public function __construct(string $key, ?bool $ascending = null)
  {
    parent::__construct(key: $key, ascending: $ascending, dialect: SQLDialect::SQLITE);
  }
}
