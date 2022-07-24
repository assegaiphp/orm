<?php

namespace Assegaiphp\Orm\Queries\Sql;

use Assegaiphp\Orm\Traits\ExecutableTrait;

final class SQLHavingClause
{
  use ExecutableTrait;

  /**
   * @param SQLQuery $query
   * @param string $condition
   */
  public function __construct(
    private readonly SQLQuery $query,
    private readonly string $condition
  )
  {
    $this->query->appendQueryString("HAVING $condition");
  }

  /**
   * @param string $condition
   * @return $this
   */
  public function or(string $condition): SQLHavingClause
  {
    $this->query->appendQueryString("OR $condition");
    return $this;
  }

  /**
   * @param string $condition
   * @return $this
   */
  public function and(string $condition): SQLHavingClause
  {
    $this->query->appendQueryString("AND $condition");
    return $this;
  }
}