<?php

namespace Assegai\Orm\Queries\MariaDb;

use Assegai\Orm\Queries\MySql\MySQLSelectDefinition;

/**
 * MariaDB-specific SELECT builder.
 *
 * MariaDB currently shares the same select-specific fluent options as MySQL,
 * so the builder reuses the MySQL implementation directly.
 */
class MariaDbSelectDefinition extends MySQLSelectDefinition
{
}