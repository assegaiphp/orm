<?php

namespace Assegai\Orm\Queries\Sql;

/**
 * Base fluent builder for table rename statements.
 */
class SQLRenameTableStatement
{
  protected string $queryString = '';

  /**
   * Create a new table rename statement.
   *
   * @param SQLQuery $query The query instance being built.
   * @param string $oldTableName The current table name.
   * @param string $newTableName The new table name.
   */
  public function __construct(
    protected readonly SQLQuery $query,
    protected readonly string $oldTableName,
    protected readonly string $newTableName,
  ) {
    $this->queryString = $this->buildRenameTableQuery();
    $this->query->setQueryString($this->queryString);
  }

  /**
   * Build the SQL string for the rename operation.
   *
   * @return string Returns the SQL query string for the rename operation.
   */
  protected function buildRenameTableQuery(): string
  {
    $quotedOldTableName = $this->query->quoteIdentifier($this->oldTableName);
    $quotedNewTableName = $this->query->quoteIdentifier($this->newTableName);

    return "RENAME TABLE $quotedOldTableName TO $quotedNewTableName";
  }

  /**
   * Return the compiled rename statement SQL.
   *
   * @return string Returns the compiled SQL string.
   */
  public function queryString(): string
  {
    return $this->queryString;
  }

  /**
   * Execute the rename statement.
   *
   * @return SQLQueryResult Returns the query execution result.
   */
  public function execute(): SQLQueryResult
  {
    return $this->query->execute();
  }
}