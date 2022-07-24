<?php

namespace Assegai\Orm\Queries\Sql;

final class SQLKeyPart
{
  private string $queryString = '';
  /**
   * @param string $key The name of the key part
   * @param bool|null $ascending Sort specifier. If set to `true`, appends 
   * `ASC` to resulting Sql. If set to `false`, appends `DESC` to
   * resulting Sql. If set to `null` then ommits sorting string.
   */
  public function __construct(
    private readonly string $key,
    private readonly ?bool $ascending = null
  )
  {
    $this->queryString = "$this->key";
    if (!is_null($this->ascending))
    {
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
}