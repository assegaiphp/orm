<?php

namespace Assegai\Orm\Queries\MariaDb;

use Assegai\Orm\Queries\MySql\MySQLJoinExpression;

/**
 * MariaDB-specific JOIN-expression builder.
 */
class MariaDbJoinExpression extends MySQLJoinExpression
{
  /**
   * Add an ON clause and keep the fluent chain on the MariaDB builder path.
   *
   * @param string $searchCondition The join condition to append.
   * @return MariaDbJoinSpecification Returns the MariaDB join-specification builder.
   */
  public function on(string $searchCondition): MariaDbJoinSpecification
  {
    return $this->createJoinSpecification(conditionOrList: $searchCondition);
  }

  /**
   * Add a USING clause and keep the fluent chain on the MariaDB builder path.
   *
   * @param array $joinColumnList The join columns to include in the USING clause.
   * @return MariaDbJoinSpecification Returns the MariaDB join-specification builder.
   */
  public function using(array $joinColumnList): MariaDbJoinSpecification
  {
    return $this->createJoinSpecification(conditionOrList: $joinColumnList, isUsing: true);
  }

  /**
   * Create the MariaDB join-specification builder.
   *
   * @param string|array $conditionOrList The join condition or USING column list.
   * @param bool $isUsing Whether the specification should compile as USING.
   * @return MariaDbJoinSpecification Returns the MariaDB join-specification builder.
   */
  protected function createJoinSpecification(string|array $conditionOrList, bool $isUsing = false): MariaDbJoinSpecification
  {
    return new MariaDbJoinSpecification(query: $this->query, conditionOrList: $conditionOrList, isUsing: $isUsing);
  }
}
