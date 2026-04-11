<?php

namespace Assegai\Orm\Queries\MariaDb;

use Assegai\Orm\Queries\MySql\MySQLInsertIntoPriority;

/**
 * MariaDB-specific insert-priority constants.
 *
 * MariaDB currently shares the same insert priority keywords as the MySQL
 * family, so this class preserves a typed MariaDB namespace.
 */
class MariaDbInsertIntoPriority extends MySQLInsertIntoPriority
{
}
