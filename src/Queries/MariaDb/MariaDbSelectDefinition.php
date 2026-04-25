<?php

namespace Assegai\Orm\Queries\MariaDb;

use Assegai\Orm\Queries\MySql\MySQLSelectDefinition;

/**
 * MariaDB-specific SELECT builder.
 *
 * MariaDB currently shares the same select-specific fluent options as MySQL,
 * so the builder reuses the MySQL implementation directly.
 */
class MariaDbSelectDefinition extends MySQLSelectDefinition
{
  /**
   * Mark the SELECT statement as HIGH_PRIORITY while keeping the fluent
   * chain on the MariaDB builder path.
   *
   * @return self Returns the current MariaDB select builder for fluent chaining.
   */
  public function highPriority(): self
  {
    parent::highPriority();

    return $this;
  }

  /**
   * Select all columns or the provided column list and keep the fluent chain
   * on the MariaDB expression path.
   *
   * @param array $columns The columns to include in the SELECT list.
   * @return MariaDbSelectExpression Returns the MariaDB select expression builder.
   */
  public function all(array $columns = []): MariaDbSelectExpression
  {
    return parent::all($columns);
  }

  /**
   * Select a COUNT aggregate and keep the fluent chain on the MariaDB
   * expression path.
   *
   * @param array $columns The columns to count.
   * @return MariaDbSelectExpression Returns the MariaDB select expression builder.
   */
  public function count(array $columns = []): MariaDbSelectExpression
  {
    return parent::count($columns);
  }

  /**
   * Select an AVG aggregate and keep the fluent chain on the MariaDB
   * expression path.
   *
   * @param array $columns The columns to average.
   * @return MariaDbSelectExpression Returns the MariaDB select expression builder.
   */
  public function avg(array $columns = []): MariaDbSelectExpression
  {
    return parent::avg($columns);
  }

  /**
   * Select a SUM aggregate and keep the fluent chain on the MariaDB
   * expression path.
   *
   * @param array $columns The columns to sum.
   * @return MariaDbSelectExpression Returns the MariaDB select expression builder.
   */
  public function sum(array $columns = []): MariaDbSelectExpression
  {
    return parent::sum($columns);
  }

  /**
   * Append the selection fragment and create the typed MariaDB select expression.
   *
   * @param string $selection The rendered selection fragment.
   * @return MariaDbSelectExpression Returns the MariaDB select expression builder.
   */
  protected function createTypedExpression(string $selection): MariaDbSelectExpression
  {
    $this->query->appendQueryString($selection);

    return new MariaDbSelectExpression(query: $this->query);
  }
}