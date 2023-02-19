<?php

namespace Unit\mocks;

use Assegai\Orm\Attributes\Columns\Column;
use Assegai\Orm\Attributes\Columns\PrimaryGeneratedColumn;
use Assegai\Orm\Attributes\Entity;
use Assegai\Orm\Queries\Sql\ColumnType;
use Assegai\Orm\Traits\ChangeRecorderTrait;

#[Entity(table: 'mocks')]
class MockEntity
{
  use ChangeRecorderTrait;

  #[PrimaryGeneratedColumn]
  public int $id = 0;

  #[Column(type: ColumnType::VARCHAR, nullable: false)]
  public string $name = '';

  #[Column(type: ColumnType::TEXT)]
  public string $description = '';
}