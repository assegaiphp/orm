<?php

namespace Assegai\Orm\Queries\SQLite;

use Assegai\Orm\Queries\Sql\SQLSelectDefinition;

/**
 * SQLite-specific SELECT builder.
 *
 * SQLite currently uses the shared SQL select behaviour without additional
 * dialect-only fluent helpers.
 */
class SQLiteSelectDefinition extends SQLSelectDefinition
{
  /**
   * Select all columns or the provided column list and keep the fluent chain
   * on the SQLite expression path.
   *
   * @param array $columns The columns to include in the SELECT list.
   * @return SQLiteSelectExpression Returns the SQLite select expression builder.
   */
  public function all(array $columns = []): SQLiteSelectExpression
  {
    return $this->createTypedExpression($this->getColumnListString(columns: $columns));
  }

  /**
   * Select a COUNT aggregate and keep the fluent chain on the SQLite
   * expression path.
   *
   * @param array $columns The columns to count.
   * @return SQLiteSelectExpression Returns the SQLite select expression builder.
   */
  public function count(array $columns = []): SQLiteSelectExpression
  {
    return $this->createTypedExpression('COUNT(' . $this->getColumnListString(columns: $columns) . ') as total');
  }

  /**
   * Select an AVG aggregate and keep the fluent chain on the SQLite
   * expression path.
   *
   * @param array $columns The columns to average.
   * @return SQLiteSelectExpression Returns the SQLite select expression builder.
   */
  public function avg(array $columns = []): SQLiteSelectExpression
  {
    return $this->createTypedExpression('AVG(' . $this->getColumnListString(columns: $columns) . ')');
  }

  /**
   * Select a SUM aggregate and keep the fluent chain on the SQLite
   * expression path.
   *
   * @param array $columns The columns to sum.
   * @return SQLiteSelectExpression Returns the SQLite select expression builder.
   */
  public function sum(array $columns = []): SQLiteSelectExpression
  {
    return $this->createTypedExpression('SUM(' . $this->getColumnListString(columns: $columns) . ')');
  }

  /**
   * Append the selection fragment and create the typed SQLite select expression.
   *
   * @param string $selection The rendered selection fragment.
   * @return SQLiteSelectExpression Returns the SQLite select expression builder.
   */
  protected function createTypedExpression(string $selection): SQLiteSelectExpression
  {
    $this->query->appendQueryString($selection);

    return new SQLiteSelectExpression(query: $this->query);
  }
}
