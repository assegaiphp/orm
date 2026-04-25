<?php

namespace Assegai\Orm\Queries\MariaDb;

use Assegai\Orm\Queries\MySql\MySQLRenameDatabaseStatement;

/**
 * MariaDB-specific database rename helper.
 *
 * MariaDB currently shares the same legacy helper rendering as the MySQL
 * family, so this class preserves a typed MariaDB namespace.
 */
class MariaDbRenameDatabaseStatement extends MySQLRenameDatabaseStatement
{
}
