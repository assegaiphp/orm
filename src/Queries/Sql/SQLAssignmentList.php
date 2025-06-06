<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Traits\ExecutableTrait;

final class SQLAssignmentList
{
  use ExecutableTrait;

  /**
   * @param SQLQuery $query
   * @param array $assignmentList
   */
  public function __construct(
    private readonly SQLQuery $query,
    private readonly array $assignmentList
  )
  {
    $queryString = 'SET ';
    $separator = ', ';
    foreach ($assignmentList as $key => $value)
    {
      if (in_array($key, $this->query->passwordHashFields()))
      {
        $value = password_hash($value, $this->query->passwordHashAlgorithm());
      }
      $queryString .= is_numeric($value)
        ? "`$key`={$value}{$separator}"
        : (is_null($value) ? "`$key`=NULL" : "`$key`='$value'{$separator}");
    }
    $queryString = trim($queryString, $separator);
    $this->query->appendQueryString( tail: $queryString );
  }

  /**
   * @param string $condition
   * @return SQLWhereClause
   */
  public function where(string $condition): SQLWhereClause
  {
    return new SQLWhereClause( query: $this->query, condition: $condition );
  }
}
