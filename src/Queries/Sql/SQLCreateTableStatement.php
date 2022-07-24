<?php

namespace Assegaiphp\Orm\Queries\Sql;

final class SQLCreateTableStatement
{
  /**
   * @param SQLQuery $query
   * @param string $tableName
   * @param bool $isTemporary
   * @param bool $checkIfNotExists
   */
  public function __construct(
    private readonly SQLQuery $query,
    private readonly string   $tableName,
    private readonly bool     $isTemporary,
    private readonly bool $checkIfNotExists
  )
  {
    $queryString = "CREATE ";
    if ($isTemporary)
    {
      $queryString .= "TEMPORARY ";
    }
    $queryString .= "TABLE ";
    if ($checkIfNotExists)
    {
      $queryString .= "IF NOT EXISTS ";
    }
    $queryString .= "$tableName";
    $this->query->setQueryString(queryString: $queryString);
  }

  /**
   * @param array $columns
   * @return SQLTableOptions
   */
  public function columns(array $columns): SQLTableOptions
  {
    return new SQLTableOptions( query: $this->query, columns: $columns );
  }
}
