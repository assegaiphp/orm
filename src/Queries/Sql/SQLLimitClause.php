<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Enumerations\SQLDialect;
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
    $queryString = match (true) {
      is_null($offset) => "LIMIT $limit",
      $this->query->getDialect() === SQLDialect::POSTGRESQL => "LIMIT $limit OFFSET $offset",
      default => "LIMIT $offset,$limit",
    };

    $this->query->appendQueryString($queryString);
  }
}
