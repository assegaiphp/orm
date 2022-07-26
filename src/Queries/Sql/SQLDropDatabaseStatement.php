<?php

namespace Assegai\Orm\Queries\Sql;

final class SQLDropDatabaseStatement
{
  /**
   * @param SQLQuery $query
   * @param string $dbName
   * @param bool $checkIfExists
   */
  public function __construct(
    private readonly SQLQuery $query,
    private readonly string   $dbName,
    private readonly bool $checkIfExists = false
  )
  {
    $queryString = "DROP DATABASE ";
    if ($checkIfExists)
    {
      $queryString .= "IF EXISTS ";
    }
    $queryString .= "`$dbName`";

    $this->query->setQueryString(queryString: $queryString);
  }

  /**
   * @return SQLQueryResult
   */
  public function execute(): SQLQueryResult
  {
    return $this->query->execute();
  }
}