<?php

namespace Assegai\Orm\Queries\MariaDb;

use Assegai\Orm\Queries\MySql\MySQLAlterDatabaseOption;

/**
 * Fluent option builder for MariaDB ALTER DATABASE statements.
 *
 * MariaDB currently shares the same database-level ALTER options as the
 * MySQL-family path, so this class preserves a typed MariaDB fluent surface.
 */
class MariaDbAlterDatabaseOption extends MySQLAlterDatabaseOption
{
}
