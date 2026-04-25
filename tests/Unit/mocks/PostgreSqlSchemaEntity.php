<?php

namespace Unit\mocks;

use Assegai\Orm\Attributes\Columns\Column;
use Assegai\Orm\Attributes\Columns\PrimaryGeneratedColumn;
use Assegai\Orm\Attributes\Entity;
use Assegai\Orm\Attributes\SqlEntityOptions;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Queries\Sql\ColumnType;

#[Entity(
  table: 'pg_schema_mocks',
  dataSource: 'assegai_test_db',
  driver: DataSourceType::POSTGRESQL,
)]
#[SqlEntityOptions(schema: 'reporting')]
class PostgreSqlSchemaEntity
{
  #[PrimaryGeneratedColumn]
  public ?int $id = null;

  #[Column(type: ColumnType::VARCHAR, nullable: false)]
  public string $name = '';
}
