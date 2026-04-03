<?php

namespace Tests\SQLite\Fixtures;

use Assegai\Orm\Attributes\Columns\Column;
use Assegai\Orm\Attributes\Entity;
use Assegai\Orm\Queries\Sql\ColumnType;

#[Entity(table: 'uuid_mocks')]
class UuidPrimaryMockEntity
{
  #[Column(type: ColumnType::VARCHAR, lengthOrValues: 36, nullable: false, isPrimaryKey: true)]
  public string $uuid = '';

  #[Column(type: ColumnType::VARCHAR, nullable: false)]
  public string $name = '';

  #[Column(type: ColumnType::TEXT)]
  public string $description = '';
}
