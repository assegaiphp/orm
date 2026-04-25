<?php

namespace Assegai\Orm\Queries\MySql;

use Assegai\Orm\Queries\Sql\SQLRenameTableStatement;

/**
 * MySQL-specific table rename statement.
 *
 * MySQL uses the `RENAME TABLE ... TO ...` form for table renames.
 */
class MySQLRenameTableStatement extends SQLRenameTableStatement
{
}