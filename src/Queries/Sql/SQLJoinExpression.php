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

    $joinReferences = $this->formatJoinTableReferences($this->joinTableReferences);
    $queryString = match($this->joinType) {
      JoinType::LEFT_JOIN => "LEFT JOIN $joinReferences",
      JoinType::RIGHT_JOIN => "RIGHT JOIN $joinReferences",
      JoinType::INNER_JOIN => "INNER JOIN $joinReferences",
      JoinType::OUTER_JOIN => "OUTER JOIN $joinReferences",
      default => "JOIN $joinReferences"
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

  private function formatJoinTableReferences(array|string $tableReferences): string
  {
    if (is_string($tableReferences)) {
      return $this->query->quoteIdentifier($tableReferences);
    }

    $parts = [];

    foreach ($tableReferences as $alias => $reference) {
      if (is_numeric($alias)) {
        $parts[] = $this->query->quoteIdentifier((string)$reference);
        continue;
      }

      $parts[] = $this->query->quoteIdentifier((string)$reference) . ' AS ' . $this->query->quoteIdentifier((string)$alias);
    }

    return implode(', ', $parts);
  }
}
