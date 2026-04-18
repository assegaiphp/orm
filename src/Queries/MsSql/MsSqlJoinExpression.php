<?php

namespace Assegai\Orm\Queries\MsSql;

use Assegai\Orm\Queries\Sql\SQLJoinExpression;

/**
 * MSSQL-specific JOIN-expression builder.
 */
class MsSqlJoinExpression extends SQLJoinExpression
{
  /**
   * Add an ON clause and keep the fluent chain on the MSSQL builder path.
   *
   * @param string $searchCondition The join condition to append.
   * @return MsSqlJoinSpecification Returns the MSSQL join-specification builder.
   */
  public function on(string $searchCondition): MsSqlJoinSpecification
  {
    return $this->createJoinSpecification(conditionOrList: $searchCondition);
  }

  /**
   * Add a USING clause and keep the fluent chain on the MSSQL builder path.
   *
   * @param array $joinColumnList The join columns to include in the USING clause.
   * @return MsSqlJoinSpecification Returns the MSSQL join-specification builder.
   */
  public function using(array $joinColumnList): MsSqlJoinSpecification
  {
    return $this->createJoinSpecification(conditionOrList: $joinColumnList, isUsing: true);
  }

  /**
   * Create the MSSQL join-specification builder.
   *
   * @param string|array $conditionOrList The join condition or USING column list.
   * @param bool $isUsing Whether the specification should compile as USING.
   * @return MsSqlJoinSpecification Returns the MSSQL join-specification builder.
   */
  protected function createJoinSpecification(string|array $conditionOrList, bool $isUsing = false): MsSqlJoinSpecification
  {
    return new MsSqlJoinSpecification(query: $this->query, conditionOrList: $conditionOrList, isUsing: $isUsing);
  }
}
