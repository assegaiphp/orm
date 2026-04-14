<?php

namespace Assegai\Orm\Queries\MariaDb;

use Assegai\Orm\Queries\MySql\MySQLUpdateDefinition;
use Assegai\Orm\Queries\Sql\SQLQuery;

/**
 * MariaDB-specific UPDATE builder.
 */
class MariaDbUpdateDefinition extends MySQLUpdateDefinition
{
  /**
   * Create a new MariaDB UPDATE statement builder.
   *
   * @param SQLQuery $query The query instance being built.
   * @param string $tableName The target table name.
   * @param bool $lowPriority Whether LOW_PRIORITY should be applied.
   * @param bool $ignore Whether IGNORE should be applied.
   */
  public function __construct(
    protected SQLQuery $query,
    protected string $tableName,
    private readonly bool $lowPriority = false,
    private readonly bool $ignore = false,
  )
  {
    parent::__construct(
      query: $query,
      tableName: $tableName,
      lowPriority: $lowPriority,
      ignore: $ignore,
    );
  }

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
