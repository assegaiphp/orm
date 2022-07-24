<?php

namespace Assegaiphp\Orm\Queries\Sql;

use Assegaiphp\Orm\Traits\ExecutableTrait;
use Assegaiphp\Orm\Traits\SQLAggregatorTrait;

final class SQLWhereClause
{
  use ExecutableTrait;
  use SQLAggregatorTrait;

  public function __construct(
    private readonly SQLQuery $query,
    private readonly string $condition
  )
  {
    if (!empty($condition))
    {
      $this->query->appendQueryString("WHERE $condition");
    }
  }

  /**
   * @param string $condition
   * @return $this
   */
  public function or(string $condition): SQLWhereClause
  {
    $operator = $this->filterOperator('OR');
    $this->query->appendQueryString("$operator $condition");
    return $this;
  }

  /**
   * @param string $condition
   * @return $this
   */
  public function and(string $condition): SQLWhereClause
  {
    $operator = $this->filterOperator('AND');
    $this->query->appendQueryString("$operator $condition");
    return $this;
  }

  /**
   * @param string $operator
   * @return string
   */
  private function filterOperator(string $operator): string
  {
    return str_contains((string)$this->query, 'WHERE') ? $operator : 'WHERE';
  }
}