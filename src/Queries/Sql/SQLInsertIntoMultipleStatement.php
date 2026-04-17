<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Traits\ExecutableTrait;

/**
 * Inserts multiple rows into an existing table.
 */
class SQLInsertIntoMultipleStatement
{
  use ExecutableTrait;

  /**
   * Tracks insert values that must be hashed before they are persisted.
   *
   * @var array<int, int>
   */
  protected array $hashableIndexes = [];

  /**
   * Creates a multi-row INSERT statement and appends its column list to the owning query.
   *
   * @param SQLQuery $query Receives the rendered INSERT fragments.
   * @param array<int|string, string> $columns The target columns for the INSERT statement.
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
   * Appends a VALUES list for a multi-row INSERT.
   *
   * @param array<int, array<int|string, mixed>> $rowsList The rows to insert.
   * @return static Returns the current insert builder for fluent chaining.
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
