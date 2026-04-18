<?php

namespace Assegai\Orm\Queries\MsSql;

use Assegai\Orm\Queries\Sql\SQLSelectDefinition;

/**
 * MSSQL-specific SELECT builder.
 */
class MsSqlSelectDefinition extends SQLSelectDefinition
{
  /**
   * Add a TOP clause to the SELECT statement.
   *
   * @param int $rowCount The maximum number of rows to return.
   * @return self Returns the current select builder for fluent chaining.
   */
  public function top(int $rowCount): self
  {
    if (!preg_match('/^SELECT\s+TOP\s*\(/i', $this->query->queryString())) {
      $this->query->setQueryString(
        str_replace('SELECT ', "SELECT TOP ($rowCount) ", $this->query->queryString())
      );
    }

    return $this;
  }

  /**
   * Select all columns or the provided column list and keep the fluent chain
   * on the MSSQL expression path.
   *
   * @param array $columns The columns to include in the SELECT list.
   * @return MsSqlSelectExpression Returns the MSSQL select expression builder.
   */
  public function all(array $columns = []): MsSqlSelectExpression
  {
    return parent::all($columns);
  }

  /**
   * Select a COUNT aggregate and keep the fluent chain on the MSSQL expression path.
   *
   * @param array $columns The columns to count.
   * @return MsSqlSelectExpression Returns the MSSQL select expression builder.
   */
  public function count(array $columns = []): MsSqlSelectExpression
  {
    return parent::count($columns);
  }

  /**
   * Select an AVG aggregate and keep the fluent chain on the MSSQL expression path.
   *
   * @param array $columns The columns to average.
   * @return MsSqlSelectExpression Returns the MSSQL select expression builder.
   */
  public function avg(array $columns = []): MsSqlSelectExpression
  {
    return parent::avg($columns);
  }

  /**
   * Select a SUM aggregate and keep the fluent chain on the MSSQL expression path.
   *
   * @param array $columns The columns to sum.
   * @return MsSqlSelectExpression Returns the MSSQL select expression builder.
   */
  public function sum(array $columns = []): MsSqlSelectExpression
  {
    return parent::sum($columns);
  }

  /**
   * Append the selection fragment and create the typed MSSQL select expression.
   *
   * @param string $selection The rendered selection fragment.
   * @return MsSqlSelectExpression Returns the MSSQL select expression builder.
   */
  protected function createTypedExpression(string $selection): MsSqlSelectExpression
  {
    $this->query->appendQueryString($selection);

    return new MsSqlSelectExpression(query: $this->query);
  }
}
