<?php

namespace Assegaiphp\Orm\Queries\Sql;

use Assegaiphp\Orm\Traits\ExecutableTrait;

final class SQLLimitClause
{
  use ExecutableTrait;

  /**
   * @param SQLQuery $query
   * @param int $limit
   * @param int|null $offset
   */
  public function __construct(
    private readonly SQLQuery $query,
    private readonly int      $limit,
    private readonly ?int     $offset = null,
  )
  {
    $queryString = "LIMIT " . (!is_null($offset) ? "$offset,$limit" : "$limit");
    $this->query->appendQueryString($queryString);
  }
}