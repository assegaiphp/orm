<?php

namespace Assegai\Orm\Queries\Sql;

final class SQLRenameDatabaseStatement
{
  private string $queryString = '';

  /**
   * @param SQLQuery $query
   * @param string $oldDbName
   * @param string $newDbName
   */
  public function __construct(
    private readonly SQLQuery $query,
    private readonly string   $oldDbName,
    private readonly string   $newDbName,
  )
  {
    $this->queryString = "CREATE DATABASE `$newDbName` / DROP DATABASE `$oldDbName`";
    $this->query->setQueryString($this->queryString);
  }

  /**
   * @return SQLQueryResult
   */
  public function execute(): SQLQueryResult
  {
    return $this->query->execute();
  }
}