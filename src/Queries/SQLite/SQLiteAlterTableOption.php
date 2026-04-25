<?php

namespace Assegai\Orm\Queries\SQLite;

use Assegai\Orm\Queries\Sql\SQLAlterTableOption;

/**
 * SQLite-specific ALTER TABLE builder.
 *
 * SQLite currently uses the shared SQL alter-table operations exposed by the
 * base builder.
 */
class SQLiteAlterTableOption extends SQLAlterTableOption
{
}