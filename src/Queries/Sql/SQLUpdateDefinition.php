<?php

namespace Assegai\Orm\Queries\Sql;

/**
 * Class SQLUpdateDefinition
 *
 * @package Assegai\Orm\Queries\Sql
 */
class SQLUpdateDefinition
{
  /**
   * @param SQLQuery $query The SQL query object.
   * @param string $tableName The name of the table to update.
   */
  public function __construct(
    protected SQLQuery $query,
    protected string   $tableName,
  )
  {
    $this->query->setQueryString('UPDATE ' . $this->query->quoteIdentifier($this->tableName));
  }

  /**
   * @param array $assignmentList
   * @return SQLAssignmentList
   */
  public function set(array $assignmentList): SQLAssignmentList
  {
    return new SQLAssignmentList(
      query: $this->query,
      assignmentList: $assignmentList
    );
  }
}