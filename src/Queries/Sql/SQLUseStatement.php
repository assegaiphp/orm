<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Queries\MySql\MySQLUseStatement;

/**
 * Backward-compatible wrapper for the old shared USE statement builder.
 *
 * @deprecated Use Assegai\Orm\Queries\MySql\MySQLUseStatement or
 *             Assegai\Orm\Queries\MariaDb\MariaDbUseStatement instead.
 */
class SQLUseStatement extends MySQLUseStatement
{
}
