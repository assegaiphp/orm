<?php

namespace Assegai\Orm\Migrations;

use Assegai\Orm\Attributes\Columns\Column;
use Assegai\Orm\Attributes\Entity;
use Assegai\Orm\Queries\Sql\ColumnType;

#[Entity(table: '__assegai_schema_migrations')]
class SchemaMigrationsEntity
{
  #[Column(name: 'migration', type: ColumnType::VARCHAR, lengthOrValues: 50, nullable: false, isPrimaryKey: true)]
  public string $name = '';

  #[Column(name: 'ran_on', type: ColumnType::DATETIME, nullable: false, default: 'CURRENT_TIMESTAMP')]
  public string $ranOn = '';
}