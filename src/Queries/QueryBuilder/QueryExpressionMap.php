<?php

namespace Assegai\Orm\Queries\QueryBuilder;

use Assegai\Orm\Interfaces\DataSourceInterface;

/**
 * Class QueryExpressionMap.
 *
 * @package Assegai\Orm\Queries\QueryBuilder
 */
class QueryExpressionMap
{
  public function __construct(
    protected DataSourceInterface $connection,
    protected ?Alias $mainAlias = null
  )
  {
  }
}