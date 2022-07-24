<?php

namespace Assegai\Orm\Exceptions;

class DataSourceException extends ORMException
{
  public function __construct($message)
  {
    parent::__construct(sprintf("Data Source error: %s", $message));
  }
}