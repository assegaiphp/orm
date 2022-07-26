<?php

namespace Assegai\Orm\Exceptions;

use Assegai\Orm\Enumerations\DataSourceType;

class DataSourceConnectionException extends DataSourceException
{
  public function __construct(DataSourceType $type = DataSourceType::MYSQL)
  {
    parent::__construct(sprintf("Connection Error: %s", $type->value));
  }
}