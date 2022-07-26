<?php

namespace Assegai\Orm\Queries\Sql;

final class SQLSelectExpression
{
  /**
   * @param SQLQuery $query
   */
  public function __construct(private readonly SQLQuery $query) { }

  /**
   * @param array|string $tableReferences
   * @return SQLTableReference
   */
  public function from(array|string $tableReferences): SQLTableReference
  {
    return new SQLTableReference( query: $this->query, tableReferences: $tableReferences );
  }
}