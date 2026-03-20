<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Management\Options\FindOptions;
use Assegai\Orm\Management\Options\FindWhereOptions;
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
    private readonly string|array|FindOptions|FindWhereOptions $condition
  )
  {
    $condition = $this->compileCondition($this->condition);

    if (!empty($condition)) {
      if (!str_contains($this->query->queryString(), 'WHERE')) {
       $this->query->appendQueryString("WHERE " . $this->filterConditionColumnNames($condition));
      } else {
        $this->query->replaceWhereClause($condition);
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

  /**
   * @param string|array|FindOptions|FindWhereOptions $condition
   * @return string
   */
  private function compileCondition(string|array|FindOptions|FindWhereOptions $condition): string
  {
    if ($condition instanceof FindOptions) {
      $condition = $condition->where ?? '';
    }

    if ($condition instanceof FindWhereOptions) {
      return $condition->compile($this->query);
    }

    if (is_array($condition)) {
      return (new FindWhereOptions(conditions: $condition))->compile($this->query);
    }

    return $condition;
  }
}
