<?php

namespace Assegai\Orm\Queries\Sql;

use JsonSerializable;

/**
 * Represents a key part of an SQL query.
 *
 * This class is used to construct SQL queries with specific key parts,
 * including optional sorting specifications (ascending or descending).
 */
final class SQLKeyPart implements JsonSerializable
{
  /**
   * The SQL string representation of the key part.
   *
   * @var string The SQL string representation of the key part.
   */
  private string $queryString = '';

  /**
   * SQLKeyPart constructor.
   *
   * @param string $key The name of the key part
   * @param bool|null $ascending Sort specifier. If set to `true`, appends 
   * `ASC` to resulting Sql. If set to `false`, appends `DESC` to
   * resulting Sql. If set to `null` then omits sorting string.
   */
  public function __construct(
    private readonly string $key,
    private readonly ?bool $ascending = null
  )
  {
    $this->queryString = "$this->key";

    if (!is_null($this->ascending)) {
      $this->queryString .= $this->ascending ? ' ASC' : ' DESC';
    }
  }

  /**
   * @return string
   */
  public function __toString(): string
  {
    return $this->queryString;
  }

  /**
   * @inheritDoc
   */
  public function jsonSerialize(): array
  {
    return [$this->key => $this->ascending ? 'ASC' : 'DESC'];
  }
}