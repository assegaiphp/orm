<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Queries\MySql\MySQLInsertIntoPriority;

/**
 * Backward-compatible wrapper for the old shared insert-priority constants.
 *
 * @deprecated Use Assegai\Orm\Queries\MySql\MySQLInsertIntoPriority or
 *             Assegai\Orm\Queries\MariaDb\MariaDbInsertIntoPriority instead.
 */
class SQLInsertIntoPriority extends MySQLInsertIntoPriority
{
}
