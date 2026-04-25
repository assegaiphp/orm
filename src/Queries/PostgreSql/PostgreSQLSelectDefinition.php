<?php

namespace Assegai\Orm\Queries\PostgreSql;

use Assegai\Orm\Queries\Sql\SQLSelectDefinition;

/**
 * PostgreSQL-specific SELECT builder.
 */
class PostgreSQLSelectDefinition extends SQLSelectDefinition
{
  /**
   * Add a DISTINCT ON clause to the SELECT statement.
   *
   * @param array $columns The columns used by DISTINCT ON.
   * @return self Returns the current select builder for fluent chaining.
   */
  public function distinctOn(array $columns): self
  {
    $columnList = $this->getColumnListString($columns);
    $this->query->appendQueryString("DISTINCT ON ($columnList)");

    return $this;
  }

  /**
   * Select all columns or the provided column list and keep the fluent chain
   * on the PostgreSQL expression path.
   *
   * @param array $columns The columns to include in the SELECT list.
   * @return PostgreSQLSelectExpression Returns the PostgreSQL select expression builder.
   */
  public function all(array $columns = []): PostgreSQLSelectExpression
  {
    return parent::all($columns);
  }

  /**
   * Select a COUNT aggregate and keep the fluent chain on the PostgreSQL
   * expression path.
   *
   * @param array $columns The columns to count.
   * @return PostgreSQLSelectExpression Returns the PostgreSQL select expression builder.
   */
  public function count(array $columns = []): PostgreSQLSelectExpression
  {
    return parent::count($columns);
  }

  /**
   * Select an AVG aggregate and keep the fluent chain on the PostgreSQL
   * expression path.
   *
   * @param array $columns The columns to average.
   * @return PostgreSQLSelectExpression Returns the PostgreSQL select expression builder.
   */
  public function avg(array $columns = []): PostgreSQLSelectExpression
  {
    return parent::avg($columns);
  }

  /**
   * Select a SUM aggregate and keep the fluent chain on the PostgreSQL
   * expression path.
   *
   * @param array $columns The columns to sum.
   * @return PostgreSQLSelectExpression Returns the PostgreSQL select expression builder.
   */
  public function sum(array $columns = []): PostgreSQLSelectExpression
  {
    return parent::sum($columns);
  }

  /**
   * Append the selection fragment and create the typed PostgreSQL select expression.
   *
   * @param string $selection The rendered selection fragment.
   * @return PostgreSQLSelectExpression Returns the PostgreSQL select expression builder.
   */
  protected function createTypedExpression(string $selection): PostgreSQLSelectExpression
  {
    $this->query->appendQueryString($selection);

    return new PostgreSQLSelectExpression(query: $this->query);
  }
}