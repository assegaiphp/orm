<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Enumerations\JoinType;

/**
 * Base JOIN-expression builder shared across SQL-family dialects.
 *
 * The class is intentionally extensible so dialect-specific subclasses can
 * keep the fluent chain typed after `join(...)->on(...)` or `join(...)->using(...)`.
 */
class SQLJoinExpression
{
  /**
   * @param SQLQuery $query
   * @param array|string $joinTableReferences
   * @param JoinType|null $joinType
   */
  public function __construct(
    protected readonly SQLQuery     $query,
    protected readonly array|string $joinTableReferences,
    protected ?JoinType             $joinType = null
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
    return $this->createJoinSpecification(conditionOrList: $searchCondition);
  }

  /**
   * @param array $joinColumnList
   * @return SQLJoinSpecification
   */
  public function using(array $joinColumnList): SQLJoinSpecification
  {
    return $this->createJoinSpecification(conditionOrList: $joinColumnList, isUsing: true);
  }

  /**
   * Create the join specification builder used by this join expression.
   *
   * Dialect-specific subclasses override this method to keep the fluent
   * chain on their own typed join specification builders.
   *
   * @param string|array $conditionOrList The join condition or USING column list.
   * @param bool $isUsing Whether the specification should compile as USING.
   * @return SQLJoinSpecification Returns the join specification builder.
   */
  protected function createJoinSpecification(string|array $conditionOrList, bool $isUsing = false): SQLJoinSpecification
  {
    return new SQLJoinSpecification(query: $this->query, conditionOrList: $conditionOrList, isUsing: $isUsing);
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
