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
    $normalizedColumns = $this->normalizeColumns($this->columns);
    $this->hashableIndexes = $this->resolveHashableIndexes($normalizedColumns);
    $this->query->appendQueryString($this->buildColumnListQueryString($normalizedColumns));
  }

  /**
   * Appends a VALUES list for a multi-row INSERT.
   *
   * @param array<int, array<int|string, mixed>> $rowsList The rows to insert.
   * @return static Returns the current insert builder for fluent chaining.
   */
  public function rows(array $rowsList): static
  {
    $this->query->appendQueryString(tail: $this->buildRowsQueryString($rowsList));
    return $this;
  }

  /**
   * Normalize qualified column references down to the insert target column names.
   *
   * @param array<int|string, string> $columns The raw column list.
   * @return array<int, string> Returns the normalized column names.
   */
  protected function normalizeColumns(array $columns): array
  {
    return array_map(function(string $column): string {
      $parts = explode('.', $column);

      return end($parts);
    }, array_values($columns));
  }

  /**
   * Resolve which column positions should be password-hashed before insert.
   *
   * @param array<int, string> $columns The normalized insert columns.
   * @return array<int, int> Returns the index positions that should be hashed.
   */
  protected function resolveHashableIndexes(array $columns): array
  {
    return array_keys(array_intersect($columns, $this->query->passwordHashFields()));
  }

  /**
   * Build the column-list fragment appended after INSERT INTO.
   *
   * @param array<int, string> $columns The normalized insert columns.
   * @return string Returns the rendered column-list fragment, or an empty string when no columns were supplied.
   */
  protected function buildColumnListQueryString(array $columns): string
  {
    if (empty($columns)) {
      return '';
    }

    $quotedColumns = array_map(
      fn(string $column): string => $this->query->quoteIdentifier($column),
      $columns
    );

    return '(' . implode(', ', $quotedColumns) . ') ';
  }

  /**
   * Build the VALUES fragment for a multi-row insert.
   *
   * @param array<int, array<int|string, mixed>> $rowsList The rows to insert.
   * @return string Returns the rendered VALUES fragment.
   */
  protected function buildRowsQueryString(array $rowsList): string
  {
    $rowGroups = array_map(
      fn(array $row): string => $this->buildRowGroup($row),
      $rowsList
    );

    return 'VALUES ' . implode(', ', $rowGroups);
  }

  /**
   * Build a single grouped VALUES row for a multi-row insert.
   *
   * @param array<int|string, mixed> $row The row values to encode.
   * @return string Returns the rendered grouped row.
   */
  protected function buildRowGroup(array $row): string
  {
    $parts = [];

    foreach ($row as $index => $value) {
      $parts[] = $this->buildValueExpression(index: $index, value: $value);
    }

    return '(' . implode(', ', $parts) . ')';
  }

  /**
   * Build a single insert value expression.
   *
   * @param int|string $index The value index within the insert payload.
   * @param mixed $value The raw value to encode.
   * @return string Returns either a placeholder or a raw SQL literal token.
   */
  protected function buildValueExpression(int|string $index, mixed $value): string
  {
    if (in_array($index, $this->hashableIndexes, true)) {
      $value = password_hash($value, $this->query->passwordHashAlgorithm());
    }

    if (is_string($value) && in_array($value, ['CURRENT_TIMESTAMP', 'NULL'], true)) {
      return $value;
    }

    return $this->query->addParam($value);
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
