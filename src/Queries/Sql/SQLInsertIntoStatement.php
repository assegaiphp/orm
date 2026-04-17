<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Traits\ExecutableTrait;

/**
 * Inserts a single row into an existing table.
 */
class SQLInsertIntoStatement
{
  use ExecutableTrait;

  /**
   * Tracks insert values that must be hashed before they are persisted.
   *
   * @var array<int, int>
   */
  protected array $hashableIndexes = [];

  /**
   * Creates a single-row INSERT statement and appends its column list to the owning query.
   *
   * @param SQLQuery $query Receives the rendered INSERT fragments.
   * @param array<int|string, string> $columns The target columns for the INSERT statement.
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
   * Appends a VALUES list for a single-row INSERT.
   *
   * @param array<int|string, mixed> $valuesList The values to insert.
   * @return static Returns the current insert builder for fluent chaining.
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

  /**
   * Converts a column selection list into SQL for dialect-specific insert clauses.
   *
   * @param array|string $columns The column names or expressions to format.
   * @return string Returns the formatted column list.
   */
  protected function getColumnListString(array|string $columns): string
  {
    if (is_string($columns)) {
      return $columns === '*'
        ? '*'
        : $this->formatColumnExpression($columns);
    }

    $parts = [];

    foreach ($columns as $key => $value) {
      $expression = $this->formatColumnExpression((string)$value);
      $parts[] = is_numeric($key)
        ? $expression
        : $expression . ' AS ' . $this->query->quoteIdentifier((string)$key);
    }

    return implode(', ', $parts);
  }

  /**
   * Converts a single column expression into the current dialect's identifier format.
   *
   * @param string $expression The column expression to format.
   * @return string Returns the formatted column expression.
   */
  protected function formatColumnExpression(string $expression): string
  {
    if ($expression === '*') {
      return '*';
    }

    return $this->query->quoteIdentifier(str_replace(['`', '"'], '', $expression));
  }
}
