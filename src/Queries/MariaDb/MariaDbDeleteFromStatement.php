<?php

namespace Assegai\Orm\Queries\MariaDb;

use Assegai\Orm\Queries\MySql\MySQLDeleteFromStatement;

/**
 * MariaDB-specific DELETE builder.
 *
 * MariaDB currently follows the same fluent delete modifiers as MySQL, so this
 * builder inherits the MySQL implementation directly.
 */
class MariaDbDeleteFromStatement extends MySQLDeleteFromStatement
{
}