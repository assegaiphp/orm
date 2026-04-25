<?php

namespace Assegai\Orm\Queries\MariaDb;

use Assegai\Orm\Queries\MySql\MySQLRenameTableStatement;

/**
 * MariaDB-specific table rename statement.
 *
 * MariaDB currently shares the same table rename syntax as MySQL.
 */
class MariaDbRenameTableStatement extends MySQLRenameTableStatement
{
}