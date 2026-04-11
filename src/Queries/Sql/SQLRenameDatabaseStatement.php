<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Queries\MySql\MySQLRenameDatabaseStatement;

/**
 * Backward-compatible wrapper for the old shared rename-database helper.
 *
 * @deprecated Use Assegai\Orm\Queries\MySql\MySQLRenameDatabaseStatement or
 *             Assegai\Orm\Queries\MariaDb\MariaDbRenameDatabaseStatement instead.
 */
class SQLRenameDatabaseStatement extends MySQLRenameDatabaseStatement
{
}
