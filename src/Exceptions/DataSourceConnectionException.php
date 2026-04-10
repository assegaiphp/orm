<?php

namespace Assegai\Orm\Exceptions;

use Assegai\Orm\Enumerations\DataSourceType;

class DataSourceConnectionException extends DataSourceException
{
  public function __construct(DataSourceType $type = DataSourceType::MYSQL, ?string $reason = null)
  {
    $message = sprintf("Connection Error: %s", $type->value);

    if ($reason) {
      $message .= " - $reason";
    }

    parent::__construct($message);
  }
}
