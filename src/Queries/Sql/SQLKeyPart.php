<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Queries\MariaDb\MariaDbKeyPart;
use Assegai\Orm\Queries\MySql\MySQLKeyPart;
use Assegai\Orm\Queries\PostgreSql\PostgreSQLKeyPart;
use Assegai\Orm\Queries\SQLite\SQLiteKeyPart;
use Assegai\Orm\Util\SqlIdentifier;
use JsonSerializable;

/**
 * Shared sort-key builder used by ORDER BY and key-definition statements.
 */
class SQLKeyPart implements JsonSerializable
{
  /**
   * The SQL string representation of the key part.
   *
   * @var string The SQL string representation of the key part.
   */
  private string $queryString = '';

  /**
   * Creates a dialect-owned key-part builder.
   *
   * @param string $key The column or expression to render.
   * @param bool|null $ascending The sort direction to append, or null to omit it.
   * @param SQLDialect $dialect The dialect that should own the builder.
   * @return self Returns the dialect-specific key-part builder.
   *
   * @throws \InvalidArgumentException Thrown when the identifier is unsafe.
   */
  public static function forDialect(
    string $key,
    ?bool $ascending = null,
    SQLDialect $dialect = SQLDialect::MYSQL
  ): self
  {
    return match ($dialect) {
      SQLDialect::MYSQL => new MySQLKeyPart(key: $key, ascending: $ascending),
      SQLDialect::POSTGRESQL => new PostgreSQLKeyPart(key: $key, ascending: $ascending),
      SQLDialect::SQLITE => new SQLiteKeyPart(key: $key, ascending: $ascending),
      SQLDialect::MARIADB => new MariaDbKeyPart(key: $key, ascending: $ascending),
    };
  }

  /**
   * Creates a key-part builder for the supplied identifier.
   *
   * @param string $key The identifier to render.
   * @param bool|null $ascending Sort specifier. If set to `true`, appends
   * `ASC` to the resulting SQL. If set to `false`, appends `DESC` to
   * the resulting SQL. If set to `null`, omits the sorting string.
   * @param SQLDialect $dialect The SQL dialect to render identifiers for.
   *
   * @throws \InvalidArgumentException Thrown when the identifier is unsafe.
   */
  public function __construct(
    private readonly string $key,
    private readonly ?bool $ascending = null,
    private readonly SQLDialect $dialect = SQLDialect::MYSQL
  )
  {
    $this->queryString = SqlIdentifier::quote($this->key, $this->dialect);

    if (!is_null($this->ascending)) {
      $this->queryString .= $this->ascending ? ' ASC' : ' DESC';
    }
  }

  /**
   * Returns the rendered SQL fragment.
   *
   * @return string Returns the rendered SQL fragment.
   */
  public function __toString(): string
  {
    return $this->queryString;
  }

  /**
   * Serializes the key part as its JSON-friendly representation.
   *
   * @return array<string, string> Returns the serialized sort direction.
   */
  public function jsonSerialize(): array
  {
    return [$this->key => $this->ascending ? 'ASC' : 'DESC'];
  }
}
