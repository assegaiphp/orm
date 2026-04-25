<?php

namespace Assegai\Orm\Queries\SQLite;

use Assegai\Orm\Queries\Sql\SQLUpdateDefinition;

/**
 * SQLite-specific UPDATE builder.
 *
 * SQLite currently relies on the shared update fluency while the dialect root
 * controls which entrypoints are exposed.
 */
class SQLiteUpdateDefinition extends SQLUpdateDefinition
{
  /**
   * Start building the SET clause and keep the fluent chain on the SQLite builder path.
   *
   * @param array $assignmentList The column/value assignments to apply.
   * @return SQLiteAssignmentList Returns the SQLite assignment-list builder.
   */
  public function set(array $assignmentList): SQLiteAssignmentList
  {
    return $this->createAssignmentList(assignmentList: $assignmentList);
  }

  /**
   * Create the SQLite assignment-list builder.
   *
   * @param array $assignmentList The column/value assignments to apply.
   * @return SQLiteAssignmentList Returns the SQLite assignment-list builder.
   */
  protected function createAssignmentList(array $assignmentList): SQLiteAssignmentList
  {
    return new SQLiteAssignmentList(query: $this->query, assignmentList: $assignmentList);
  }
}
