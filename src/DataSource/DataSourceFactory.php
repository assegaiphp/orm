<?php

namespace Assegai\Orm\DataSource;

use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Interfaces\DataSourceInterface;
use InvalidArgumentException;

class DataSourceFactory
{
  /**
   * Creates a new data source instance based on the provided data source type.
   *
   * @param DataSourceType $dataSource The data source type.
   * @return DataSourceInterface The data source instance.
   */
  public static function create(DataSourceType $dataSource, string $name, array $data = []): DataSourceInterface
  {
    switch ($dataSource) {
      case DataSourceType::MARIADB:
      case DataSourceType::MYSQL:
        return new MySQLDataSource($name);
      case DataSourceType::POSTGRESQL:
        return new PostgreSQLDataSource($name);
      case DataSourceType::SQLITE:
        return new SQLiteDataSource($name);
      case DataSourceType::REDIS:
        return new RedisDataSource($name);
      default:
        throw new InvalidArgumentException("Data source $dataSource->value is not supported.");
    }
  }
}