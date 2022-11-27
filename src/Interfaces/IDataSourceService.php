<?php

namespace Assegai\Orm\Interfaces;

use Assegai\Orm\Attributes\Entity;
use Assegai\Orm\DataSource\DataSource;

/**
 * A base type for the entities and data source available to a scope.
 */
interface IDataSourceService
{
  /**
   * @return Entity[]
   */
  public function getEntities(): array;

  /**
   * @return DataSource
   */
  public function getDataSource(): DataSource;
}