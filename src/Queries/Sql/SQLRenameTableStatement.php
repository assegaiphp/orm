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
    $this->queryString = $this->buildQueryString();
    $this->query->setQueryString($this->queryString);
  }

  /**
   * Build the SQL string for the rename operation.
   *
   * @return string Returns the SQL query string for the rename operation.
   */
  protected function buildQueryString(): string
  {
    return $this->buildRenamePrefix() . ' ' .
      $this->buildOldTableExpression() . ' ' .
      $this->buildRenameTargetClause() . ' ' .
      $this->buildNewTableExpression();
  }

  /**
   * Build the rename prefix for the active SQL dialect.
   *
   * @return string Returns the leading rename clause.
   */
  protected function buildRenamePrefix(): string
  {
    return 'RENAME TABLE';
  }

  /**
   * Build the quoted old table identifier.
   *
   * @return string Returns the quoted old table expression.
   */
  protected function buildOldTableExpression(): string
  {
    return $this->query->quoteIdentifier($this->oldTableName);
  }

  /**
   * Build the rename target clause between the old and new table names.
   *
   * @return string Returns the rename target clause.
   */
  protected function buildRenameTargetClause(): string
  {
    return 'TO';
  }

  /**
   * Build the quoted new table identifier.
   *
   * @return string Returns the quoted new table expression.
   */
  protected function buildNewTableExpression(): string
  {
    return $this->query->quoteIdentifier($this->newTableName);
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
