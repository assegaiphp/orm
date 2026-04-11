<?php

namespace Assegai\Orm\Queries\PostgreSql;

use Assegai\Orm\Queries\Sql\SQLUpdateDefinition;

/**
 * PostgreSQL-specific UPDATE builder.
 *
 * PostgreSQL currently relies on the shared update fluency while the dialect
 * root controls which entrypoints are exposed.
 */
class PostgreSQLUpdateDefinition extends SQLUpdateDefinition
{
}