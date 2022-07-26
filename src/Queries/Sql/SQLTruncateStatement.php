<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Traits\ExecutableTrait;

final class SQLTruncateStatement
{
  use ExecutableTrait;

  /**
   * @param SQLQuery $query
   * @param string $tableName
   */
  public function __construct(
    private readonly SQLQuery $query,
    private readonly string $tableName
  )
  {
    $this->query->setQueryString(queryString: "TRUNCATE TABLE `$tableName`");
  }
}