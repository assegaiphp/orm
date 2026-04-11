<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Traits\ExecutableTrait;

class SQLInsertIntoMultipleStatement
{
  use ExecutableTrait;

  protected array $hashableIndexes = [];

  /**
   * @param SQLQuery $query The SQLQuery object.
   * @param array $columns A parenthesized list of comma-separated column names for which the statment provides values.
   */
  public function __construct(
    protected readonly SQLQuery $query,
    protected readonly array $columns
  )
  {
    $queryString = '';
    $columns = array_map(function(string $column): string {
      $parts = explode('.', $column);
      return end($parts);
    }, array_values($columns));

    if (!empty($columns)) {
      $quotedColumns = array_map(fn(string $column): string => $this->query->quoteIdentifier($column), $columns);
      $queryString = '(' . implode(', ', $quotedColumns) . ') ';
      $this->hashableIndexes = array_keys(array_intersect($columns, $this->query->passwordHashFields()));
    }

    $this->query->appendQueryString($queryString);
  }

  /**
   * @param array $rowsList
   * @return static
   */
  public function rows(array $rowsList): static
  {
    $rowGroups = [];
    $separator = ', ';

    foreach ($rowsList as $row) {
      $rowQuery = '';

      foreach ($row as $index => $value) {
        if (in_array($index, $this->hashableIndexes, true)) {
          $value = password_hash($value, $this->query->passwordHashAlgorithm());
        }

        if (is_string($value) && in_array($value, ['CURRENT_TIMESTAMP', 'NULL'], true)) {
          $rowQuery .= $value . $separator;
          continue;
        }

        $rowQuery .= $this->query->addParam($value) . $separator;
      }

      $rowGroups[] = '(' . trim($rowQuery, $separator) . ')';
    }

    $this->query->appendQueryString(tail: 'VALUES ' . implode($separator, $rowGroups));
    return $this;
  }
}