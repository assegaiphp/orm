<?php

namespace Assegai\Orm\Queries\Sql;

/**
 * Shared INSERT INTO entry point for SQL-family query builders.
 */
class SQLInsertIntoDefinition
{
  /**
   * Creates a shared INSERT INTO definition and primes the owning query string.
   *
   * @param SQLQuery $query Receives the rendered INSERT statement fragments.
   * @param string $tableName The table targeted by the INSERT statement.
   */
  public function __construct(
    protected readonly SQLQuery $query,
    protected readonly string $tableName
  )
  {
    $queryString = 'INSERT INTO ' . $this->query->quoteIdentifier($this->tableName) . ' ';

    $this->query->setQueryString($queryString);
  }

  /**
   * Begins a single-row INSERT statement.
   *
   * @param array<int|string, string> $columns The target columns for the INSERT statement.
   * @return SQLInsertIntoStatement Returns the single-row INSERT builder.
   */
  public function singleRow(array $columns = []): SQLInsertIntoStatement
  {
    return $this->createSingleRowStatement(columns: $columns);
  }

  /**
   * Begins a multi-row INSERT statement.
   *
   * @param array<int|string, string> $columns The target columns for the INSERT statement.
   * @return SQLInsertIntoMultipleStatement Returns the multi-row INSERT builder.
   */
  public function multipleRows(array $columns = []): SQLInsertIntoMultipleStatement
  {
    return $this->createMultipleRowsStatement(columns: $columns);
  }

  /**
   * Creates the single-row INSERT builder for the active SQL dialect.
   *
   * @param array<int|string, string> $columns The target columns for the INSERT statement.
   * @return SQLInsertIntoStatement Returns the single-row INSERT builder.
   */
  protected function createSingleRowStatement(array $columns = []): SQLInsertIntoStatement
  {
    return new SQLInsertIntoStatement(
      query: $this->query,
      columns: $columns,
    );
  }

  /**
   * Creates the multi-row INSERT builder for the active SQL dialect.
   *
   * @param array<int|string, string> $columns The target columns for the INSERT statement.
   * @return SQLInsertIntoMultipleStatement Returns the multi-row INSERT builder.
   */
  protected function createMultipleRowsStatement(array $columns = []): SQLInsertIntoMultipleStatement
  {
    return new SQLInsertIntoMultipleStatement(
      query: $this->query,
      columns: $columns,
    );
  }
}
