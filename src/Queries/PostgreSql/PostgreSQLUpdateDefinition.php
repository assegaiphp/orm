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
  /**
   * Start building the SET clause and keep the fluent chain on the PostgreSQL builder path.
   *
   * @param array $assignmentList The column/value assignments to apply.
   * @return PostgreSQLAssignmentList Returns the PostgreSQL assignment-list builder.
   */
  public function set(array $assignmentList): PostgreSQLAssignmentList
  {
    return $this->createAssignmentList(assignmentList: $assignmentList);
  }

  /**
   * Create the PostgreSQL assignment-list builder.
   *
   * @param array $assignmentList The column/value assignments to apply.
   * @return PostgreSQLAssignmentList Returns the PostgreSQL assignment-list builder.
   */
  protected function createAssignmentList(array $assignmentList): PostgreSQLAssignmentList
  {
    return new PostgreSQLAssignmentList(query: $this->query, assignmentList: $assignmentList);
  }
}
