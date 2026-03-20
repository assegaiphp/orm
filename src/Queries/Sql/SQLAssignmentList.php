<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Management\Options\FindOptions;
use Assegai\Orm\Management\Options\FindWhereOptions;
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
      if (is_string($value) && $value === 'CURRENT_TIMESTAMP') {
        $queryString .= "`$key`={$value}{$separator}";
        continue;
      }

      $queryString .= "`$key`=" . $this->query->addParam($value) . $separator;
    }
    $queryString = trim($queryString, $separator);
    $this->query->appendQueryString( tail: $queryString );
  }

  /**
   * @param string|array|FindOptions|FindWhereOptions $condition
   * @return SQLWhereClause
   */
  public function where(string|array|FindOptions|FindWhereOptions $condition): SQLWhereClause
  {
    return new SQLWhereClause( query: $this->query, condition: $condition );
  }
}
