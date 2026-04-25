<?php

namespace Assegai\Orm\Queries\MariaDb;

use Assegai\Orm\Queries\MySql\MySQLUpdateDefinition;

/**
 * MariaDB-specific UPDATE builder.
 */
class MariaDbUpdateDefinition extends MySQLUpdateDefinition
{
  /**
   * Start building the SET clause and keep the fluent chain on the MariaDB builder path.
   *
   * @param array $assignmentList The column/value assignments to apply.
   * @return MariaDbAssignmentList Returns the MariaDB assignment-list builder.
   */
  public function set(array $assignmentList): MariaDbAssignmentList
  {
    return $this->createAssignmentList(assignmentList: $assignmentList);
  }

  /**
   * Create the MariaDB assignment-list builder.
   *
   * @param array $assignmentList The column/value assignments to apply.
   * @return MariaDbAssignmentList Returns the MariaDB assignment-list builder.
   */
  protected function createAssignmentList(array $assignmentList): MariaDbAssignmentList
  {
    return new MariaDbAssignmentList(query: $this->query, assignmentList: $assignmentList);
  }
}
