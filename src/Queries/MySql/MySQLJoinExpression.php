<?php

namespace Assegai\Orm\Queries\MySql;

use Assegai\Orm\Queries\Sql\SQLJoinExpression;

/**
 * MySQL-specific JOIN-expression builder.
 */
class MySQLJoinExpression extends SQLJoinExpression
{
  /**
   * Add an ON clause and keep the fluent chain on the MySQL builder path.
   *
   * @param string $searchCondition The join condition to append.
   * @return MySQLJoinSpecification Returns the MySQL join-specification builder.
   */
  public function on(string $searchCondition): MySQLJoinSpecification
  {
    return $this->createJoinSpecification(conditionOrList: $searchCondition);
  }

  /**
   * Add a USING clause and keep the fluent chain on the MySQL builder path.
   *
   * @param array $joinColumnList The join columns to include in the USING clause.
   * @return MySQLJoinSpecification Returns the MySQL join-specification builder.
   */
  public function using(array $joinColumnList): MySQLJoinSpecification
  {
    return $this->createJoinSpecification(conditionOrList: $joinColumnList, isUsing: true);
  }

  /**
   * Create the MySQL join-specification builder.
   *
   * @param string|array $conditionOrList The join condition or USING column list.
   * @param bool $isUsing Whether the specification should compile as USING.
   * @return MySQLJoinSpecification Returns the MySQL join-specification builder.
   */
  protected function createJoinSpecification(string|array $conditionOrList, bool $isUsing = false): MySQLJoinSpecification
  {
    return new MySQLJoinSpecification(query: $this->query, conditionOrList: $conditionOrList, isUsing: $isUsing);
  }
}
