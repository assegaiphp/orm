<?php

namespace Assegai\Orm\Queries\SQLite;

use Assegai\Orm\Queries\Sql\SQLSelectDefinition;

/**
 * SQLite-specific SELECT builder.
 *
 * SQLite currently uses the shared SQL select behaviour without additional
 * dialect-only fluent helpers.
 */
class SQLiteSelectDefinition extends SQLSelectDefinition
{
}