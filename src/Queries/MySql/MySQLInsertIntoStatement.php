<?php

namespace Assegai\Orm\Queries\MySql;

use Assegai\Orm\Queries\Sql\SQLInsertIntoStatement;
use Assegai\Orm\Traits\DuplicateKeyUpdatableTrait;

class MySQLInsertIntoStatement extends SQLInsertIntoStatement
{
  use DuplicateKeyUpdatableTrait;
}