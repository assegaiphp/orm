<?php

namespace Assegai\Orm\DataSource;

use Assegai\Core\Attributes\Injectable;
use Assegai\Orm\Attributes\DataSourceConfig;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Exceptions\DataSourceException;
use Assegai\Orm\Interfaces\IDataSourceService;
use ReflectionClass;

#[Injectable, DataSourceConfig(type: DataSourceType::MYSQL, name: '', entities: [])]
class AbstractDataSourceService implements IDataSourceService
{
  /**
   * @var DataSource
   */
  protected readonly DataSource $dataSource;

  /**
   * Constructs a DataSourceService.
   * @throws DataSourceException
   */
  public final function __construct()
  {
    $reflection = new ReflectionClass($this);
    $attributes = $reflection->getAttributes(DataSourceConfig::class);

    if (! $attributes )
    {
      throw new DataSourceException(message: 'DataSourceConfig attribute not found.');
    }

    foreach ($attributes as $attribute)
    {
      /** @var DataSourceConfig $attributeInstance */
      $attributeInstance = $attribute->newInstance();
      $this->entities = $attributeInstance->entities;
      $type = match(gettype($attributeInstance->type)) {
        'string' => DataSourceType::from($attributeInstance->type),
        'NULL' => DataSourceType::MYSQL,
        default => $attributeInstance->type
      };
      $this->dataSource = new DataSource(
        new DataSourceOptions(
          entities: $attributeInstance->entities,
          name: $attributeInstance->name,
          type: $type,
          host: $attributeInstance->host,
          port: $attributeInstance->port,
          username: $attributeInstance->user,
          password: $attributeInstance->password,
          synchronize: $attributeInstance->synchronize
        )
      );
    }
  }

  /**
   * @inheritDoc
   */
  public function getEntities(): array
  {
    return $this->dataSource->entities;
  }

  /**
   * @inheritDoc
   */
  public function getDataSource(): DataSource
  {
    return $this->dataSource;
  }
}