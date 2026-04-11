<?php

namespace Assegai\Orm\Queries\MySql;

use Assegai\Orm\Queries\Sql\SQLUpdateDefinition;

class MySQLUpdateDefinition extends SQLUpdateDefinition
{
  public function __construct(
    protected \Assegai\Orm\Queries\Sql\SQLQuery $query,
    protected string $tableName,
    private bool $lowPriority = false,
    private bool $ignore = false,
  )
  {
    $queryString = 'UPDATE ';

    if ($this->lowPriority) {
      $queryString .= 'LOW_PRIORITY ';
    }

    if ($this->ignore) {
      $queryString .= 'IGNORE ';
    }

    $this->query->setQueryString(trim($queryString) . ' ' . $this->query->quoteIdentifier($this->tableName));
  }
}