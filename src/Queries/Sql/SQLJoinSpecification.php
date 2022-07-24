<?php

namespace Assegaiphp\Orm\Queries\Sql;

use Assegaiphp\Orm\Traits\ExecutableTrait;
use Assegaiphp\Orm\Traits\JoinableTrait;

final class SQLJoinSpecification
{
  use ExecutableTrait;
  use JoinableTrait;

  /**
   * @param SQLQuery $query
   * @param string|array $conditionOrList
   * @param bool $isUsing
   */
  public function __construct(
    private readonly SQLQuery     $query,
    private readonly string|array $conditionOrList,
    private readonly bool $isUsing = false
  ) {
    $specification =
      is_array($conditionOrList)
      ? '(' . implode(',', $conditionOrList) . ')'
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
    return new SQLWhereClause(query: $this->query, condition: $condition);
  }
}