<?php

namespace Assegai\Orm\Queries\SQLite;

use Assegai\Orm\Queries\PostgreSql\PostgreSQLRenameTableStatement;

/**
 * SQLite-specific table rename statement.
 *
 * SQLite currently uses the same `ALTER TABLE ... RENAME TO ...` shape as PostgreSQL.
 */
class SQLiteRenameTableStatement extends PostgreSQLRenameTableStatement
{
}