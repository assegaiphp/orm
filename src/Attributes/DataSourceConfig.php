<?php

namespace Assegai\Orm\Attributes;

use Assegai\Orm\Enumerations\DataSourceType;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class DataSourceConfig
{
  /**
   * Constructs a DataSourceConfig attribute.
   * @param DataSourceType|string|null $type
   * @param string|null $name
   * @param string $host
   * @param string $user
   * @param string $password
   * @param int $port
   * @param array $entities
   * @param bool $synchronize
   */
  public function __construct(
    public readonly DataSourceType|string|null $type,
    public readonly ?string $name,
    public readonly string $host = 'localhost',
    public readonly string $user = 'root',
    public readonly string $password = '',
    public readonly int $port = 3306,
    public readonly array $entities = [],
    public readonly bool $synchronize = false,
  )
  {
  }
}