<?php

namespace Assegai\Orm\Queries\MySql;

use Assegai\Orm\Queries\Sql\SQLSelectDefinition;

/**
 * MySQL-specific SELECT builder.
 */
class MySQLSelectDefinition extends SQLSelectDefinition
{
  /**
   * Mark the SELECT statement as HIGH_PRIORITY.
   *
   * @return self Returns the current select builder for fluent chaining.
   */
  public function highPriority(): self
  {
    if (!str_starts_with($this->query->queryString(), 'SELECT HIGH_PRIORITY ')) {
      $this->query->setQueryString(
        str_replace('SELECT ', 'SELECT HIGH_PRIORITY ', $this->query->queryString())
      );
    }

    return $this;
  }

  /**
   * Select all columns or the provided column list and keep the fluent chain
   * on the MySQL expression path.
   *
   * @param array $columns The columns to include in the SELECT list.
   * @return MySQLSelectExpression Returns the MySQL select expression builder.
   */
  public function all(array $columns = []): MySQLSelectExpression
  {
    return parent::all($columns);
  }

  /**
   * Select a COUNT aggregate and keep the fluent chain on the MySQL
   * expression path.
   *
   * @param array $columns The columns to count.
   * @return MySQLSelectExpression Returns the MySQL select expression builder.
   */
  public function count(array $columns = []): MySQLSelectExpression
  {
    return parent::count($columns);
  }

  /**
   * Select an AVG aggregate and keep the fluent chain on the MySQL
   * expression path.
   *
   * @param array $columns The columns to average.
   * @return MySQLSelectExpression Returns the MySQL select expression builder.
   */
  public function avg(array $columns = []): MySQLSelectExpression
  {
    return parent::avg($columns);
  }

  /**
   * Select a SUM aggregate and keep the fluent chain on the MySQL
   * expression path.
   *
   * @param array $columns The columns to sum.
   * @return MySQLSelectExpression Returns the MySQL select expression builder.
   */
  public function sum(array $columns = []): MySQLSelectExpression
  {
    return parent::sum($columns);
  }

  /**
   * Append the selection fragment and create the typed MySQL select expression.
   *
   * @param string $selection The rendered selection fragment.
   * @return MySQLSelectExpression Returns the MySQL select expression builder.
   */
  protected function createTypedExpression(string $selection): MySQLSelectExpression
  {
    $this->query->appendQueryString($selection);

    return new MySQLSelectExpression(query: $this->query);
  }
}