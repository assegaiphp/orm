<?php

namespace Unit\mocks;

use Assegai\Orm\Attributes\Columns\Column;
use Assegai\Orm\Attributes\Columns\PrimaryGeneratedColumn;
use Assegai\Orm\Attributes\Entity;
use Assegai\Orm\Attributes\SqlEntityOptions;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Queries\Sql\ColumnType;

#[Entity(
  table: 'sqlite_without_rowid',
  dataSource: 'sqlite_test_db',
  driver: DataSourceType::SQLITE,
)]
#[SqlEntityOptions(withRowId: false)]
class SqliteWithoutRowIdEntity
{
  #[PrimaryGeneratedColumn]
  public ?int $id = null;

  #[Column(type: ColumnType::VARCHAR, nullable: false)]
  public string $name = '';
}
