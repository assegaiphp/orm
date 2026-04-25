<?php

namespace Assegai\Orm\Queries\PostgreSql;

use Assegai\Orm\Queries\Sql\SQLJoinExpression;

/**
 * PostgreSQL-specific JOIN-expression builder.
 */
class PostgreSQLJoinExpression extends SQLJoinExpression
{
  /**
   * Add an ON clause and keep the fluent chain on the PostgreSQL builder path.
   *
   * @param string $searchCondition The join condition to append.
   * @return PostgreSQLJoinSpecification Returns the PostgreSQL join-specification builder.
   */
  public function on(string $searchCondition): PostgreSQLJoinSpecification
  {
    return $this->createJoinSpecification(conditionOrList: $searchCondition);
  }

  /**
   * Add a USING clause and keep the fluent chain on the PostgreSQL builder path.
   *
   * @param array $joinColumnList The join columns to include in the USING clause.
   * @return PostgreSQLJoinSpecification Returns the PostgreSQL join-specification builder.
   */
  public function using(array $joinColumnList): PostgreSQLJoinSpecification
  {
    return $this->createJoinSpecification(conditionOrList: $joinColumnList, isUsing: true);
  }

  /**
   * Create the PostgreSQL join-specification builder.
   *
   * @param string|array $conditionOrList The join condition or USING column list.
   * @param bool $isUsing Whether the specification should compile as USING.
   * @return PostgreSQLJoinSpecification Returns the PostgreSQL join-specification builder.
   */
  protected function createJoinSpecification(string|array $conditionOrList, bool $isUsing = false): PostgreSQLJoinSpecification
  {
    return new PostgreSQLJoinSpecification(query: $this->query, conditionOrList: $conditionOrList, isUsing: $isUsing);
  }
}
