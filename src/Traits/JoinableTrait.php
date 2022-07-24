<?php

namespace Assegai\Orm\Traits;

use Assegai\Orm\Enumerations\JoinType;
use Assegai\Orm\Queries\Sql\SQLJoinExpression;

trait JoinableTrait
{
  public function join(array|string $tableReferences): SQLJoinExpression
  {
    return new SQLJoinExpression(query: $this->query, joinTableReferences: $tableReferences, joinType: JoinType::JOIN);
  }

  public function leftJoin(array|string $tableReferences): SQLJoinExpression
  {
    return new SQLJoinExpression(query: $this->query, joinTableReferences: $tableReferences, joinType: JoinType::LEFT_JOIN);
  }

  public function rightJoin(array|string $tableReferences): SQLJoinExpression
  {
    return new SQLJoinExpression(query: $this->query, joinTableReferences: $tableReferences, joinType: JoinType::RIGHT_JOIN);
  }

  public function innerJoin(array|string $tableReferences): SQLJoinExpression
  {
    return new SQLJoinExpression(query: $this->query, joinTableReferences: $tableReferences, joinType: JoinType::INNER_JOIN);
  }

  public function outerJoin(array|string $tableReferences): SQLJoinExpression
  {
    return new SQLJoinExpression(query: $this->query, joinTableReferences: $tableReferences, joinType: JoinType::OUTER_JOIN);
  }
}