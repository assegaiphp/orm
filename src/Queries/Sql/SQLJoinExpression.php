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
      JoinType::LEFT_JOIN => "LEFT JOIN $this->joinTableReferences",
      JoinType::RIGHT_JOIN => "RIGHT JOIN $this->joinTableReferences",
      JoinType::INNER_JOIN => "INNER JOIN $this->joinTableReferences",
      JoinType::OUTER_JOIN => "OUTER JOIN $this->joinTableReferences",
      default => "JOIN $this->joinTableReferences"
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