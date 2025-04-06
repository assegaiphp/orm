<?php

namespace Assegai\Orm\Exceptions;

use Assegai\Orm\Queries\Sql\SQLQuery;
use Throwable;

class GeneralSQLQueryException extends ORMException
{
  public function __construct(?SQLQuery $query = null, ?Throwable $previous = null)
  {
    $code = '';
    $info = '';
    if ($query !== null) {
      $code = $query->getConnection()->errorCode();
      $info = print_r($query?->getConnection()->errorInfo(), true);
    }

    parent::__construct(
      sprintf(
        "An error occurred while executing SQL query. \nSQL: %s\nCODE: %s\nInfo: %s\n",
        $query,
        $code,
        $info
      )
    );
  }
}