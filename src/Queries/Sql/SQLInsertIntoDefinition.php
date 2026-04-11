<?php

namespace Assegai\Orm\Queries\Sql;

class SQLInsertIntoDefinition
{
  /**
   * @param SQLQuery $query
   * @param string $tableName
   */
  public function __construct(
    protected readonly SQLQuery $query,
    protected readonly string   $tableName
  )
  {
    $queryString = 'INSERT INTO ' . $this->query->quoteIdentifier($this->tableName) . ' ';

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