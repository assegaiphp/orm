<?php

namespace Unit\mocks;

use Assegai\Orm\Attributes\Columns\Column;
use Assegai\Orm\Attributes\Columns\PrimaryGeneratedColumn;
use Assegai\Orm\Attributes\Entity;
use Assegai\Orm\Queries\Sql\ColumnType;
use Assegai\Orm\Traits\ChangeRecorderTrait;

#[Entity(
  table: 'mocks',
  database: 'assegai_test_db',
)]
class MockEntity
{
  use ChangeRecorderTrait;

  #[PrimaryGeneratedColumn]
  public ?int $id = null;

  #[Column(type: ColumnType::VARCHAR, nullable: false)]
  public string $name = '';

  #[Column(type: ColumnType::TEXT)]
  public string $description = '';

  #[Column(name: 'color_type', type: ColumnType::ENUM, default: MockColorType::RED, enum: MockColorType::class)]
  public MockColorType $colorType = MockColorType::RED;
}