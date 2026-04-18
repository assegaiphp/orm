<?php

namespace Assegai\Orm\Queries\MsSql;

use Assegai\Orm\Queries\Sql\SQLUpdateDefinition;

/**
 * MSSQL-specific UPDATE builder.
 */
class MsSqlUpdateDefinition extends SQLUpdateDefinition
{
  /**
   * Start building the SET clause and keep the fluent chain on the MSSQL builder path.
   *
   * @param array $assignmentList The column/value assignments to apply.
   * @return MsSqlAssignmentList Returns the MSSQL assignment-list builder.
   */
  public function set(array $assignmentList): MsSqlAssignmentList
  {
    return $this->createAssignmentList(assignmentList: $assignmentList);
  }

  /**
   * Create the MSSQL assignment-list builder.
   *
   * @param array $assignmentList The column/value assignments to apply.
   * @return MsSqlAssignmentList Returns the MSSQL assignment-list builder.
   */
  protected function createAssignmentList(array $assignmentList): MsSqlAssignmentList
  {
    return new MsSqlAssignmentList(query: $this->query, assignmentList: $assignmentList);
  }
}
