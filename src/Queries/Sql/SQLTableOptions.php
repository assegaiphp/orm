<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Traits\ExecutableTrait;

/**
 * Shared CREATE TABLE column-body builder.
 */
class SQLTableOptions
{
  use ExecutableTrait;

  /**
   * Appends the supplied column definitions to the owning CREATE TABLE statement.
   *
   * @param SQLQuery $query Receives the rendered CREATE TABLE body.
   * @param array<int, mixed> $columns The column definitions to render.
   * @param string $comment Reserved for dialect-specific table comments.
   */
  public function __construct(
    protected readonly SQLQuery $query,
    protected readonly array $columns,
    protected readonly string $comment = ""
  )
  {
    $primaryKeyAlreadySet = false;
    $queryString = "(";
    foreach ($this->columns as $column)
    {
      $column = strval($column);

      if (str_contains($column, 'PRIMARY KEY'))
      {
        if ($primaryKeyAlreadySet)
        {
          $column = str_replace('PRIMARY KEY', '', $column);
        }

        $primaryKeyAlreadySet = true;
      }
      $queryString .= $column . ", ";
    }
    $queryString = trim(string: $queryString, characters: ", ") . ")";
    $this->query->appendQueryString($queryString);
  }
}
