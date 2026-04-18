<?php

namespace Assegai\Orm\Queries\PostgreSql;

use Assegai\Orm\Queries\Sql\SQLRenameTableStatement;

/**
 * PostgreSQL-specific table rename statement.
 *
 * PostgreSQL uses the `ALTER TABLE ... RENAME TO ...` form for table renames.
 */
class PostgreSQLRenameTableStatement extends SQLRenameTableStatement
{
  /**
   * Build the PostgreSQL-specific rename prefix.
   *
   * @return string Returns the PostgreSQL rename prefix.
   */
  protected function buildRenamePrefix(): string
  {
    return 'ALTER TABLE';
  }

  /**
   * Build the PostgreSQL-specific rename target clause.
   *
   * @return string Returns the PostgreSQL rename target clause.
   */
  protected function buildRenameTargetClause(): string
  {
    return 'RENAME TO';
  }
}
