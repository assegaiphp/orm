<?php

namespace Assegaiphp\Orm\Queries\Sql;

final class SQLRenameTableStatement
{
  private string $queryString = '';

  /**
   * @param SQLQuery $query
   * @param string $oldTableName
   * @param string $newTableName
   */
  public function __construct(
    private readonly SQLQuery $query,
    private readonly string   $oldTableName,
    private readonly string   $newTableName,
  )
  {
    $this->queryString = "RENAME TABLE `$oldTableName` TO `$newTableName`";
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