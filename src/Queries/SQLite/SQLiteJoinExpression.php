<?php

namespace Assegai\Orm\Queries\SQLite;

use Assegai\Orm\Queries\Sql\SQLJoinExpression;

/**
 * SQLite-specific JOIN-expression builder.
 */
class SQLiteJoinExpression extends SQLJoinExpression
{
  /**
   * Add an ON clause and keep the fluent chain on the SQLite builder path.
   *
   * @param string $searchCondition The join condition to append.
   * @return SQLiteJoinSpecification Returns the SQLite join-specification builder.
   */
  public function on(string $searchCondition): SQLiteJoinSpecification
  {
    return $this->createJoinSpecification(conditionOrList: $searchCondition);
  }

  /**
   * Add a USING clause and keep the fluent chain on the SQLite builder path.
   *
   * @param array $joinColumnList The join columns to include in the USING clause.
   * @return SQLiteJoinSpecification Returns the SQLite join-specification builder.
   */
  public function using(array $joinColumnList): SQLiteJoinSpecification
  {
    return $this->createJoinSpecification(conditionOrList: $joinColumnList, isUsing: true);
  }

  /**
   * Create the SQLite join-specification builder.
   *
   * @param string|array $conditionOrList The join condition or USING column list.
   * @param bool $isUsing Whether the specification should compile as USING.
   * @return SQLiteJoinSpecification Returns the SQLite join-specification builder.
   */
  protected function createJoinSpecification(string|array $conditionOrList, bool $isUsing = false): SQLiteJoinSpecification
  {
    return new SQLiteJoinSpecification(query: $this->query, conditionOrList: $conditionOrList, isUsing: $isUsing);
  }
}
