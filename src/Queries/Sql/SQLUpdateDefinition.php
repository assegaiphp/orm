<?php

namespace Assegai\Orm\Queries\Sql;

/**
 * Base fluent builder for UPDATE statements shared across SQL dialects.
 */
class SQLUpdateDefinition
{
  /**
   * Create a new UPDATE statement builder.
   *
   * @param SQLQuery $query The query instance being built.
   * @param string $tableName The target table name.
   */
  public function __construct(
    protected SQLQuery $query,
    protected string $tableName,
  )
  {
    $this->query->setQueryString($this->buildQueryString());
  }

  /**
   * Build the initial UPDATE statement for the active SQL-family builder.
   *
   * @return string Returns the rendered UPDATE statement.
   */
  protected function buildQueryString(): string
  {
    return $this->buildUpdatePrefix() . ' ' . $this->buildTableExpression();
  }

  /**
   * Build the UPDATE prefix clause.
   *
   * @return string Returns the leading UPDATE clause.
   */
  protected function buildUpdatePrefix(): string
  {
    return 'UPDATE';
  }

  /**
   * Build the table expression for the update target.
   *
   * @return string Returns the quoted table expression.
   */
  protected function buildTableExpression(): string
  {
    return $this->query->quoteIdentifier($this->tableName);
  }

  /**
   * Start building the SET clause for the UPDATE statement.
   *
   * @param array $assignmentList The column/value assignments to apply.
   * @return SQLAssignmentList Returns the assignment-list builder for continued fluent chaining.
   */
  public function set(array $assignmentList): SQLAssignmentList
  {
    return $this->createAssignmentList(assignmentList: $assignmentList);
  }

  /**
   * Create the assignment-list builder used by this UPDATE statement.
   *
   * Dialect-specific subclasses override this method to keep the fluent
   * chain on their own typed SET-clause builders.
   *
   * @param array $assignmentList The column/value assignments to apply.
   * @return SQLAssignmentList Returns the assignment-list builder.
   */
  protected function createAssignmentList(array $assignmentList): SQLAssignmentList
  {
    return new SQLAssignmentList(query: $this->query, assignmentList: $assignmentList);
  }
}
