<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Traits\ExecutableTrait;

final class SQLDescribeStatement
{
  use ExecutableTrait;

  /**
   * @param SQLQuery $query
   * @param string $subject
   */
  public function __construct(
    private readonly SQLQuery $query,
    private readonly string $subject
  )
  {
    $this->query->appendQueryString("DESCRIBE $this->subject");
  }
}
