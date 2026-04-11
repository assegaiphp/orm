<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Queries\MySql\MySQLAlterDatabaseOption;

/**
 * Backward-compatible wrapper for the old shared ALTER DATABASE option class.
 *
 * @deprecated Use Assegai\Orm\Queries\MySql\MySQLAlterDatabaseOption or
 *             Assegai\Orm\Queries\MariaDb\MariaDbAlterDatabaseOption instead.
 */
class SQLAlterDatabaseOption extends MySQLAlterDatabaseOption
{
}
