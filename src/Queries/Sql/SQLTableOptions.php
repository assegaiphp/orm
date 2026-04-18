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
    $this->query->appendQueryString($this->buildQueryString());
  }

  /**
   * Build the CREATE TABLE body for the active SQL-family builder.
   *
   * @return string Returns the rendered CREATE TABLE body.
   */
  protected function buildQueryString(): string
  {
    return '(' . $this->buildColumnDefinitions() . ')';
  }

  /**
   * Build the comma-delimited column definition list.
   *
   * @return string Returns the rendered column definitions.
   */
  protected function buildColumnDefinitions(): string
  {
    $primaryKeyAlreadySet = false;
    $parts = [];

    foreach ($this->columns as $column) {
      $normalizedColumn = $this->normalizeColumnDefinition(
        column: (string)$column,
        primaryKeyAlreadySet: $primaryKeyAlreadySet,
      );

      if ($normalizedColumn === '') {
        continue;
      }

      if (str_contains($normalizedColumn, 'PRIMARY KEY')) {
        $primaryKeyAlreadySet = true;
      }

      $parts[] = $normalizedColumn;
    }

    return implode(', ', $parts);
  }

  /**
   * Normalize a single column definition before it is appended to the table body.
   *
   * @param string $column The raw column definition.
   * @param bool $primaryKeyAlreadySet Whether a PRIMARY KEY has already been emitted.
   * @return string Returns the normalized column definition.
   */
  protected function normalizeColumnDefinition(string $column, bool $primaryKeyAlreadySet): string
  {
    if ($primaryKeyAlreadySet && str_contains($column, 'PRIMARY KEY')) {
      return trim(str_replace('PRIMARY KEY', '', $column));
    }

    return $column;
  }
}
