<?php

namespace Assegai\Orm\Queries\Sql;

/**
 * Class SQLUpdateDefinition
 *
 * @package Assegai\Orm\Queries\Sql
 */
final readonly class SQLUpdateDefinition
{
  /**
   * @param SQLQuery $query The SQL query object.
   * @param string $tableName The name of the table to update.
   * @param bool $lowPriority Whether to use low priority. When set to true, the update will be delayed until there 
   * are no clients reading from the table.
   * @param bool $ignore Whether to ignore errors.
   */
  public function __construct(
    private SQLQuery $query,
    private string   $tableName,
    private bool     $lowPriority = false,
    private bool     $ignore =  false
  )
  {
    $queryString = "UPDATE ";

    if ($this->lowPriority) {
      $queryString .= "LOW_PRIORITY ";
    }

    if ($this->ignore) {
      $queryString .= "IGNORE ";
    }

    $queryString = trim($queryString);
    $this->query->setQueryString(queryString: "$queryString `$this->tableName`");
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