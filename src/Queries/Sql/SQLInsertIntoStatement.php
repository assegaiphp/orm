<?php

namespace Assegaiphp\Orm\Queries\Sql;

use Assegaiphp\Orm\Traits\DuplicateKeyUpdatableTrait;
use Assegaiphp\Orm\Traits\ExecutableTrait;

/**
 * Inserts new rows into an existing table.
 */
final class SQLInsertIntoStatement
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
    private readonly array $columns = []
  )
  {
    $queryString = "";

    if (!empty($columns))
    {
      $queryString = "(" . implode(', ', $columns) . ") ";
      
      $columnIndex = 0;
      foreach ($columns as $index => $column)
      {
        if (is_numeric($index))
        {
          if(in_array($column, $this->query->passwordHashFields()))
          {
            $this->hashableIndexes[] = $index;
          }
        }
        else
        {
          if (in_array($index, $this->query->passwordHashFields()))
          {
            $this->hashableIndexes[] = $columnIndex;
          }
        }
        ++$columnIndex;
      }
    }

    $this->query->appendQueryString($queryString);
  }

  /**
   * @param array $valuesList
   * @return $this
   */
  public function values(array $valuesList): SQLInsertIntoStatement
  {
    $queryString = "VALUES(";
    $separator = ', ';
    $quoteExemptions = ['CURRENT_TIMESTAMP', 'NULL'];

    foreach ($valuesList as $index => $value)
    {
      if (in_array($index, $this->hashableIndexes))
      {
        $value = password_hash($value, $this->query->passwordHashAlgorithm());
      }
      if (is_bool($value))
      {
        $value = (int)$value;
      }
      if (is_null($value))
      {
        $value = 'NULL';
      }
      $queryString .=
        (is_numeric($value) || in_array($value, $quoteExemptions))
        ? "$value$separator"
        : "'$value'$separator";
    }

    $queryString = trim(string: $queryString, characters: $separator) . ") ";
    $this->query->appendQueryString($queryString);
    return $this;
  }
}