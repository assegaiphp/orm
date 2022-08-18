<?php

namespace Assegai\Orm\Relations;

class JoinTableOptions
{
  /**
   * @param null|string $name
   * Name of the table that will be created to store values of the both tables (join table).
   * By default is auto generated.
   * @param null|JoinColumnOptions $joinColumn First column of the join table.
   * @param null|JoinColumnOptions $inverseJoinColumn Second (inverse) column of the join table.
   * @param null|string $database Database where join table will be created.
   * Works only in some databases (like mysql and mssql).
   * @param null|string $schema
   * Schema where join table will be created.
   * Works only in some databases (like postgres and mssql).
   */
  public function __construct(
    public readonly ?string $name = null,
    public readonly ?JoinColumnOptions $joinColumn = null,
    public readonly ?JoinColumnOptions $inverseJoinColumn = null,
    public readonly ?string $database = null,
    public readonly ?string $schema = null,
  )
  {
  }
}