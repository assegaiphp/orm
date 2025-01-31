<?php

namespace Assegai\Orm\Queries\QueryBuilder;

use Assegai\Orm\Interfaces\DataSourceInterface;
use Assegai\Orm\Queries\Sql\SQLQuery;

abstract class AbstractQueryBuilder
{
  protected readonly QueryExpressionMap $expressionMap;

  public function __construct(
    protected readonly DataSourceInterface $connection,
    protected readonly SQLQuery $query
  )
  {
    $this->expressionMap = new QueryExpressionMap($this->connection);
  }

  /**
   * Get the generated SQL query without parameters being replaced.
   *
   * @return string The generated SQL query.
   */
  public function getQuery(): string
  {
    return $this->query->queryString();
  }

  /**
   * Gets the main alias string used in this query builder.
   *
   * @return string The main alias string.
   */
  public function getAlias(): string
  {

    return $this->expressionMap->getMainAlias()->getName();
  }
}