<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Traits\ExecutableTrait;

/**
 * Base LIMIT-clause builder shared across SQL-family dialects.
 *
 * Dialect-specific subclasses keep the fluent chain typed after
 * `from(...)->limit(...)` or `where(...)->limit(...)`.
 */
class SQLLimitClause
{
  use ExecutableTrait;

  /**
   * @param SQLQuery $query
   * @param int $limit
   * @param int|null $offset
   */
  public function __construct(
    protected readonly SQLQuery $query,
    protected readonly int      $limit,
    protected readonly ?int     $offset = null,
  )
  {
    $this->query->appendQueryString($this->buildQueryString());
  }

  /**
   * Build the LIMIT fragment for the active SQL-family builder.
   *
   * @return string Returns the rendered LIMIT fragment.
   */
  protected function buildQueryString(): string
  {
    return $this->offset === null
      ? $this->buildLimitOnlyQueryString()
      : $this->buildOffsetLimitQueryString();
  }

  /**
   * Build the LIMIT fragment used when only a limit value is present.
   *
   * @return string Returns the rendered LIMIT-only fragment.
   */
  protected function buildLimitOnlyQueryString(): string
  {
    return "LIMIT {$this->limit}";
  }

  /**
   * Build the LIMIT fragment used when both limit and offset values are present.
   *
   * @return string Returns the rendered LIMIT/OFFSET fragment.
   */
  protected function buildOffsetLimitQueryString(): string
  {
    return "LIMIT {$this->offset},{$this->limit}";
  }
}
