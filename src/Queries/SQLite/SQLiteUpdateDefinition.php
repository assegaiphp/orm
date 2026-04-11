<?php

namespace Assegai\Orm\Queries\SQLite;

use Assegai\Orm\Queries\Sql\SQLUpdateDefinition;

/**
 * SQLite-specific UPDATE builder.
 *
 * SQLite currently relies on the shared update fluency while the dialect root
 * controls which entrypoints are exposed.
 */
class SQLiteUpdateDefinition extends SQLUpdateDefinition
{
}