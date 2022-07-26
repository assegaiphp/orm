<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Traits\ExecutableTrait;

final class SQLTableOptions
{
  use ExecutableTrait;

  /**
   * @param SQLQuery $query
   * @param array $columns
   * @param string $comment
   */
  public function __construct(
    private readonly SQLQuery $query,
    private readonly array    $columns,
    private readonly string $comment = ""
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