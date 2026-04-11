<?php

namespace Assegai\Orm\Queries\MySql;

use Assegai\Orm\Queries\Sql\SQLInsertIntoMultipleStatement;
use Assegai\Orm\Traits\DuplicateKeyUpdatableTrait;

class MySQLInsertIntoMultipleStatement extends SQLInsertIntoMultipleStatement
{
  use DuplicateKeyUpdatableTrait;
}