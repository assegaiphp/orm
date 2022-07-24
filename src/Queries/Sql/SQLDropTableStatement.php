<?php

namespace Assegaiphp\Orm\Queries\Sql;

use Assegaiphp\Orm\Traits\ExecutableTrait;

/**
 * Removes one or more tables. You must have the [DROP](https://dev.mysql.com/doc/refman/8.0/en/privileges-provided.html#priv_drop) privelege for each table.
 */
final class SQLDropTableStatement
{
  use ExecutableTrait;

  /**
   * @param SQLQuery $query
   * @param string $tableName
   * @param bool $checkIfExists
   */
  public function __construct(
    private readonly SQLQuery $query,
    private readonly string   $tableName,
    private readonly bool $checkIfExists = true
  )
  {
    $queryString = "DROP TABLE ";
    if ($checkIfExists)
    {
      $queryString .= "IF EXISTS ";
    }
    $queryString .= "`$tableName`";
    $this->query->setQueryString(queryString: $queryString);
  }
}