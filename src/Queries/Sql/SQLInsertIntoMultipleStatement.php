<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Traits\DuplicateKeyUpdatableTrait;
use Assegai\Orm\Traits\ExecutableTrait;

final class SQLInsertIntoMultipleStatement
{
  use DuplicateKeyUpdatableTrait;
  use ExecutableTrait;

  private array $hashableIndexes = [];

  /**
   * @param SQLQuery $query The SQLQuery object.
   * @param array $columns A parenthesized list of comma-separated column names for which the statment provides values.
   */
  public function __construct(
    private readonly SQLQuery $query,
    private readonly array $columns
  )
  {
    $queryString = "";
    
    if (!empty($columns))
    {
      $queryString = "(" . implode(', ', $columns) . ") ";
      $this->hashableIndexes = array_keys( array_intersect( $columns, $this->query->passwordHashFields() ) );
    }

    $this->query->appendQueryString($queryString);
  }

  /**
   * @param array $rowsList
   * @return $this
   */
  public function rows(array $rowsList): SQLInsertIntoMultipleStatement
  {
    $queryString = "VALUES ";
    $separator = ', ';

    foreach ($rowsList as $row)
    {
      $queryString .= "ROW(";
      foreach ($row as $index => $value)
      {
        if (in_array($index, $this->hashableIndexes))
        {
          $value = password_hash($value, $this->query->passwordHashAlgorithm());
        }
        $queryString .= is_numeric($value) ? "${value}${separator}" : "'${value}'${separator}";
      }
      $queryString = trim($queryString, $separator);
      $queryString .= ")$separator";
    }
    $queryString = trim($queryString, $separator);
    $this->query->appendQueryString(tail: $queryString);
    return $this;
  }
}