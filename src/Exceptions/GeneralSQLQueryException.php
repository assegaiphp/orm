<?php

namespace Assegai\Orm\Exceptions;

use Assegai\Orm\Queries\Sql\SQLQuery;

class GeneralSQLQueryException extends ORMException
{
  public function __construct(SQLQuery $query)
  {
    parent::__construct(sprintf("An error occurred while executing SQL query. \n%s", $query));
  }
}