<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Enumerations\JoinType;

final class SQLJoinExpression
{
  /**
   * @param SQLQuery $query
   * @param array|string $joinTableReferences
   * @param JoinType|null $joinType
   */
  public function __construct(
    private readonly SQLQuery     $query,
    private readonly array|string $joinTableReferences,
    private ?JoinType             $joinType = null
  ) {
    if (is_null($this->joinType))
    {
      $this->joinType = JoinType::JOIN;
    }

    $queryString = match($this->joinType) {
      JoinType::LEFT_JOIN => "LEFT JOIN $joinTableReferences",
      JoinType::RIGHT_JOIN => "RIGHT JOIN $joinTableReferences",
      JoinType::INNER_JOIN => "INNER JOIN $joinTableReferences",
      JoinType::OUTER_JOIN => "OUTER JOIN $joinTableReferences",
      default => "JOIN $joinTableReferences"
    };

    $this->query->appendQueryString(tail: $queryString);
  }

  /**
   * @param string $searchCondition
   * @return SQLJoinSpecification
   */
  public function on(string $searchCondition): SQLJoinSpecification
  {
    return new SQLJoinSpecification(query: $this->query, conditionOrList: $searchCondition);
  }

  /**
   * @param array $joinColumnList
   * @return SQLJoinSpecification
   */
  public function using(array $joinColumnList): SQLJoinSpecification
  {
    return new SQLJoinSpecification(query: $this->query, conditionOrList: $joinColumnList, isUsing: true);
  }
}