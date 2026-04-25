<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Enumerations\JoinType;
use Assegai\Orm\Traits\ExecutableTrait;
use Assegai\Orm\Traits\JoinableTrait;

/**
 * Base JOIN specification builder shared across SQL-family dialects.
 *
 * The class is intentionally extensible so dialect-specific subclasses can
 * keep the fluent chain typed after `on(...)`, `using(...)`, and follow-on joins.
 */
class SQLJoinSpecification
{
  use ExecutableTrait;
  use JoinableTrait;

  /**
   * @param SQLQuery $query
   * @param string|array $conditionOrList
   * @param bool $isUsing
   */
  public function __construct(
    protected readonly SQLQuery     $query,
    protected readonly string|array $conditionOrList,
    protected readonly bool $isUsing = false
  ) {
    $specification =
      is_array($conditionOrList)
      ? '(' . implode(', ', array_map(fn(string $identifier): string => $this->query->quoteIdentifier($identifier), $conditionOrList)) . ')'
      : $conditionOrList;

    $queryString = $isUsing ? "USING $specification" : "ON $specification";
    $this->query->appendQueryString(tail: $queryString);
  }

  /**
   * @param string $condition
   * @return SQLWhereClause
   */
  public function where(string $condition): SQLWhereClause
  {
    return $this->createWhereClause(condition: $condition);
  }

  /**
   * Create the WHERE clause builder used by this join specification.
   *
   * Dialect-specific subclasses override this method to keep the fluent
   * chain on their own typed WHERE builders.
   *
   * @param string $condition The WHERE condition to append.
   * @return SQLWhereClause Returns the WHERE clause builder.
   */
  protected function createWhereClause(string $condition): SQLWhereClause
  {
    return new SQLWhereClause(query: $this->query, condition: $condition);
  }

  /**
   * Create the join expression builder used by joinable methods on this specification.
   *
   * Dialect-specific subclasses override this method to keep nested joins on
   * their own typed join-expression builders.
   *
   * @param array|string $tableReferences The table name, table list, or alias map.
   * @param JoinType $joinType The join type to apply.
   * @return SQLJoinExpression Returns the join expression builder.
   */
  protected function createJoinExpression(array|string $tableReferences, JoinType $joinType): SQLJoinExpression
  {
    return new SQLJoinExpression(query: $this->query, joinTableReferences: $tableReferences, joinType: $joinType);
  }
}
