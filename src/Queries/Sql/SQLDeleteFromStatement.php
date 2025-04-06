<?php

namespace Assegai\Orm\Queries\Sql;

use Assegai\Orm\Traits\ExecutableTrait;

final class SQLDeleteFromStatement
{
  use ExecutableTrait;

  /**
   * @param SQLQuery $query
   * @param string $tableName
   * @param string|null $alias
   */
  public function __construct(
    private readonly SQLQuery $query,
    private readonly string   $tableName,
    private readonly ?string $alias = null
  )
  {
    $tableName = str_replace('`', '', $tableName);
    $queryString = "DELETE FROM `$tableName`";
    if (!is_null($alias))
    {
      $queryString .= "AS $alias";
    }
    $this->query->setQueryString(queryString: $queryString);
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
