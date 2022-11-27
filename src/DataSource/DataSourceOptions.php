<?php

namespace Assegai\Orm\DataSource;

use Assegai\Orm\Enumerations\DataSourceType;

class DataSourceOptions
{
  /**
   * @param array $entities
   * @param string $database
   * @param DataSourceType $type
   * @param string $host
   * @param int $port
   * @param string|null $username
   * @param string|null $password
   */
  public function __construct(
    public readonly array $entities,
    public readonly string $database,
    public readonly DataSourceType $type = DataSourceType::MYSQL,
    public readonly string $host = 'localhost',
    public readonly int $port = 3306,
    public readonly ?string $username = null,
    public readonly ?string $password = null,
    public readonly bool $synchronize = false,
  )
  {
  }
}