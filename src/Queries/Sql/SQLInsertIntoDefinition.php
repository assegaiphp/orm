<?php

namespace Assegai\Orm\Queries\Sql;

final readonly class SQLInsertIntoDefinition
{
  /**
   * @param SQLQuery $query
   * @param string $tableName
   */
  public function __construct(
    private SQLQuery $query,
    private string   $tableName
  )
  {
    $queryString = "INSERT INTO `$this->tableName` ";

    $this->query->setQueryString($queryString);
  }

  /**
   * @param array $columns
   * @return SQLInsertIntoStatement
   */
  public function singleRow(array $columns = []): SQLInsertIntoStatement
  {
    return new SQLInsertIntoStatement(
      query: $this->query,
      columns: $columns
    );
  }

  /**
   * @param array $columns
   * @return SQLInsertIntoMultipleStatement
   */
  public function multipleRows(array $columns = []): SQLInsertIntoMultipleStatement
  {
    return new SQLInsertIntoMultipleStatement(
      query: $this->query,
      columns: $columns
    );
  }
}