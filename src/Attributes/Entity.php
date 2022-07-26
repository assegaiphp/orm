<?php

namespace Assegai\Orm\Attributes;

use Assegai\Orm\Enumerations\DataSourceType;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Entity
{
  public function __construct(
    public ?string         $table = null,
    public ?string         $orderBy = null,
    public ?string         $engine = null,
    public ?string         $database = null,
    public ?string         $schema = null,
    public ?bool           $synchronize = true,
    public ?bool           $withRowId = false,
    public ?array          $protected = ['password'],
    public ?DataSourceType $driver = DataSourceType::MYSQL,
  )
  {
  }
}