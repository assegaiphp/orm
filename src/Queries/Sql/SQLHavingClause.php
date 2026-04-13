<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Traits\ExecutableTrait;

/**
 * Base HAVING-clause builder shared across SQL-family dialects.
 *
 * The class is intentionally extensible so dialect-specific subclasses can
 * keep the fluent chain typed after `from(...)->having(...)`.
 */
class SQLHavingClause
{
  use ExecutableTrait;

  /**
   * @param SQLQuery $query
   * @param string $condition
   */
  public function __construct(
    protected readonly SQLQuery $query,
    protected readonly string $condition
  )
  {
    $this->query->appendQueryString("HAVING $condition");
  }

  /**
   * @param string $condition
   * @return $this
   */
  public function or(string $condition): static
  {
    $this->query->appendQueryString("OR $condition");
    return $this;
  }

  /**
   * @param string $condition
   * @return $this
   */
  public function and(string $condition): static
  {
    $this->query->appendQueryString("AND $condition");
    return $this;
  }
}
