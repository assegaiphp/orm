<?php

namespace Assegai\Orm\Queries\MariaDb;

use Assegai\Orm\Queries\MySql\MySQLAlterTableOption;

/**
 * MariaDB-specific ALTER TABLE builder.
 *
 * MariaDB currently follows the same fluent alter-table operations as MySQL, so
 * this builder reuses the MySQL implementation directly.
 */
class MariaDbAlterTableOption extends MySQLAlterTableOption
{
}