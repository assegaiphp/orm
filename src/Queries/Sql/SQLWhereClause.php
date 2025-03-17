<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Traits\ExecutableTrait;
use Assegai\Orm\Traits\SQLAggregatorTrait;

/**
 * Class SQLWhereClause represents a WHERE clause in an SQL query.
 *
 * @package Assegai\Orm\Queries\Sql
 */
final class SQLWhereClause
{
  use ExecutableTrait;
  use SQLAggregatorTrait;

  /**
   * @param SQLQuery $query
   * @param string $condition
   */
  public function __construct(
    private readonly SQLQuery $query,
    private readonly string $condition
  )
  {
    if (!empty($this->condition)) {
      if (!str_contains($this->query->queryString(), 'WHERE')) {
       $this->query->appendQueryString("WHERE " . $this->filterConditionColumnNames($this->condition));
      } else {
        $this->query->replaceWhereClause($this->condition);
      }
    }
  }

  /**
   * @param string $condition
   * @return $this
   */
  public function or(string $condition): SQLWhereClause
  {
    $operator = $this->filterOperator('OR');
    $this->query->appendQueryString("$operator " . $this->filterConditionColumnNames($condition));
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

  /**
   * @param string $conditions
   * @return string
   */
  private function filterConditionColumnNames(string $conditions): string
  {
    return $conditions;
  }
}