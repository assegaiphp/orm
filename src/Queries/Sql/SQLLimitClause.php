<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Traits\ExecutableTrait;

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
    $queryString = match (true) {
      is_null($offset) => "LIMIT $limit",
      $this->query->getDialect() === SQLDialect::POSTGRESQL => "LIMIT $limit OFFSET $offset",
      default => "LIMIT $offset,$limit",
    };

    $this->query->appendQueryString($queryString);
  }
}