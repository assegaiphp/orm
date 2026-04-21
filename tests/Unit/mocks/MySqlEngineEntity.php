<?php

namespace Unit\mocks;

use Assegai\Orm\Attributes\Columns\Column;
use Assegai\Orm\Attributes\Columns\PrimaryGeneratedColumn;
use Assegai\Orm\Attributes\Entity;
use Assegai\Orm\Attributes\SqlEntityOptions;
use Assegai\Orm\Queries\Sql\ColumnType;

#[Entity(
  table: 'engine_mocks',
  dataSource: 'assegai_test_db',
)]
#[SqlEntityOptions(engine: 'MyISAM')]
class MySqlEngineEntity
{
  #[PrimaryGeneratedColumn]
  public ?int $id = null;

  #[Column(type: ColumnType::VARCHAR, nullable: false)]
  public string $name = '';
}
