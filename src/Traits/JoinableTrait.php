<?php

namespace Assegai\Orm\Traits;

use Assegai\Orm\Enumerations\JoinType;
use Assegai\Orm\Queries\Sql\SQLJoinExpression;

trait JoinableTrait
{
  /**
   * Add a JOIN clause to the current query.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return SQLJoinExpression Returns the join-expression builder.
   */
  public function join(array|string $tableReferences): SQLJoinExpression
  {
    return $this->createJoinExpression(tableReferences: $tableReferences, joinType: JoinType::JOIN);
  }

  /**
   * Add a LEFT JOIN clause to the current query.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return SQLJoinExpression Returns the join-expression builder.
   */
  public function leftJoin(array|string $tableReferences): SQLJoinExpression
  {
    return $this->createJoinExpression(tableReferences: $tableReferences, joinType: JoinType::LEFT_JOIN);
  }

  /**
   * Add a RIGHT JOIN clause to the current query.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return SQLJoinExpression Returns the join-expression builder.
   */
  public function rightJoin(array|string $tableReferences): SQLJoinExpression
  {
    return $this->createJoinExpression(tableReferences: $tableReferences, joinType: JoinType::RIGHT_JOIN);
  }

  /**
   * Add an INNER JOIN clause to the current query.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return SQLJoinExpression Returns the join-expression builder.
   */
  public function innerJoin(array|string $tableReferences): SQLJoinExpression
  {
    return $this->createJoinExpression(tableReferences: $tableReferences, joinType: JoinType::INNER_JOIN);
  }

  /**
   * Add an OUTER JOIN clause to the current query.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @return SQLJoinExpression Returns the join-expression builder.
   */
  public function outerJoin(array|string $tableReferences): SQLJoinExpression
  {
    return $this->createJoinExpression(tableReferences: $tableReferences, joinType: JoinType::OUTER_JOIN);
  }
}
