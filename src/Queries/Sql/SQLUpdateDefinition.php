<?php

namespace Assegai\Orm\Queries\Sql;

final class SQLUpdateDefinition
{
  /**
   * @param SQLQuery $query
   * @param string $tableName
   * @param bool $lowPriority
   * @param bool $ignore
   */
  public function __construct(
    private readonly SQLQuery $query,
    private readonly string   $tableName,
    private readonly bool     $lowPriority = false,
    private readonly bool     $ignore =  false
  )
  {
    $queryString = "UPDATE ";
    if ($lowPriority)
    {
      $queryString .= "LOW_PRIORITY ";
    }
    if ($ignore)
    {
      $queryString .= "IGNORE ";
    }
    $queryString = trim($queryString);
    $this->query->setQueryString(queryString: "$queryString `$tableName`");
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