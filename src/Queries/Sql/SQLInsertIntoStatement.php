<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Traits\ExecutableTrait;

/**
 * Inserts new rows into an existing table.
 */
class SQLInsertIntoStatement
{
  use ExecutableTrait;

  protected array $hashableIndexes = [];

  /**
   * @param SQLQuery $query The SQLQuery object.
   * @param array $columns A parenthesized list of comma-separated column names for which the statment provides values.
   */
  public function __construct(protected readonly SQLQuery $query, protected readonly array $columns = [])
  {
    $queryString = '';
    $columns = array_map(function(string $column): string {
      $parts = explode('.', $column);
      return end($parts);
    }, array_values($columns));

    if (!empty($columns)) {
      $quotedColumns = array_map(fn(string $column): string => $this->query->quoteIdentifier($column), $columns);
      $queryString = '(' . implode(', ', $quotedColumns) . ') ';

      $columnIndex = 0;
      foreach ($columns as $index => $column) {
        if (is_numeric($index)) {
          if (in_array($column, $this->query->passwordHashFields(), true)) {
            $this->hashableIndexes[] = $index;
          }
        } else {
          if (in_array($index, $this->query->passwordHashFields(), true)) {
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
   * @return static
   */
  public function values(array $valuesList): static
  {
    $queryString = 'VALUES(';
    $separator = ', ';

    foreach ($valuesList as $index => $value) {
      if (in_array($index, $this->hashableIndexes, true)) {
        $value = password_hash($value, $this->query->passwordHashAlgorithm());
      }

      if (is_string($value) && in_array($value, ['CURRENT_TIMESTAMP', 'NULL'], true)) {
        $queryString .= "$value$separator";
        continue;
      }

      $queryString .= $this->query->addParam($value) . $separator;
    }

    $queryString = trim(string: $queryString, characters: $separator) . ') ';
    $this->query->appendQueryString($queryString);
    return $this;
  }
}