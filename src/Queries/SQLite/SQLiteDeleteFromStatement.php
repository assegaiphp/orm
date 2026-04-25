<?php

namespace Assegai\Orm\Queries\SQLite;

use Assegai\Orm\Queries\Sql\SQLDeleteFromStatement;

/**
 * SQLite-specific DELETE builder.
 *
 * SQLite currently follows the shared SQL delete behaviour without introducing
 * any extra fluent clauses at this layer.
 */
class SQLiteDeleteFromStatement extends SQLDeleteFromStatement
{
}