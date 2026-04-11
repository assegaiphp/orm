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
    $this->query->setQueryString('UPDATE ' . $this->query->quoteIdentifier($this->tableName));
  }

  /**
   * Start building the SET clause for the UPDATE statement.
   *
   * @param array $assignmentList The column/value assignments to apply.
   * @return SQLAssignmentList Returns the assignment-list builder for continued fluent chaining.
   */
  public function set(array $assignmentList): SQLAssignmentList
  {
    return new SQLAssignmentList(
      query: $this->query,
      assignmentList: $assignmentList
    );
  }
}