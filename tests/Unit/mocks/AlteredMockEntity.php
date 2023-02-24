<?php

namespace Unit\mocks;

use Assegai\Orm\Attributes\Columns\Column;
use Assegai\Orm\Attributes\Columns\EmailColumn;
use Assegai\Orm\Attributes\Columns\PrimaryGeneratedColumn;
use Assegai\Orm\Attributes\Entity;
use Assegai\Orm\Queries\Sql\ColumnType;
use Assegai\Orm\Traits\ChangeRecorderTrait;

#[Entity(
  table: 'mocks',
  database: 'assegai_test_db',
)]
class AlteredMockEntity
{
  use ChangeRecorderTrait;

  #[PrimaryGeneratedColumn]
  public int $id = 0;

  #[Column(type: ColumnType::VARCHAR, nullable: false)]
  public string $name = '';

  #[EmailColumn]
  public string $email = '';

  #[Column(type: ColumnType::TEXT)]
  public string $description = '';
}