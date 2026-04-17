<?php

namespace Assegai\Orm\Queries\Sql;

/**
 * Base fluent builder for RENAME statements.
 *
 * Dialect-specific subclasses can return typed rename-table builders while the
 * shared SQL family still exposes a consistent rename entrypoint.
 */
class SQLRenameStatement
{
  /**
   * Create a new RENAME statement builder.
   *
   * @param SQLQuery $query The query instance being built.
   */
  public function __construct(
    protected readonly SQLQuery $query,
  ) {
  }

  /**
   * Begin a table rename operation.
   *
   * @param string $from The current table name.
   * @param string $to The new table name.
   * @return SQLRenameTableStatement Returns the base rename-table builder.
   */
  public function table(string $from, string $to): SQLRenameTableStatement
  {
    return $this->createRenameTableStatement(from: $from, to: $to);
  }

  /**
   * Create the rename-table builder for this dialect.
   *
   * Dialect-specific subclasses override this method to keep the fluent path
   * on their own typed rename-table builders.
   *
   * @param string $from The current table name.
   * @param string $to The new table name.
   * @return SQLRenameTableStatement Returns the rename-table builder.
   */
  protected function createRenameTableStatement(string $from, string $to): SQLRenameTableStatement
  {
    return new SQLRenameTableStatement(query: $this->query, oldTableName: $from, newTableName: $to);
  }
}
