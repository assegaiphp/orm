<?php

namespace Assegai\Orm\DataSource;

use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Interfaces\DataSourceInterface;
use Assegai\Orm\Support\OrmRuntime;
use InvalidArgumentException;
use Predis\Client;

/**
 * RedisDataSource class.
 *
 * This class is responsible for managing the connection to a Redis data source.
 */
class RedisDataSource implements DataSourceInterface
{
  const DEFAULT_SCHEME = 'tcp';
  const DEFAULT_HOST = '127.0.0.1';
  const DEFAULT_PORT = 6379;
  const DEFAULT_DATABASE = 0;

  protected DataSourceType $type = DataSourceType::REDIS;
  protected Client $client;

  public function __construct(
    protected string $name,
  )
  {
    $databases = OrmRuntime::databaseConfigs();

    if (!isset($databases[$this->type->value]) || !isset($databases[$this->type->value][$name])) {
      throw new InvalidArgumentException("Database $name not found.");
    }

    $config = $databases[$this->type->value][$name];
    extract($config);
    $scheme ??= self::DEFAULT_SCHEME;
    $host ??= self::DEFAULT_HOST;
    $port ??= self::DEFAULT_PORT;
    $password ??= '';
    $database ??= self::DEFAULT_DATABASE;

    $this->client = new Client([
      'scheme' => $scheme,
      'host' => $host,
      'port' => $port,
      'password' => $password,
      'database' => $database,
    ]);
  }

  public function connect(DataSourceOptions|array|null $options): void
  {
    $this->client->connect();
  }

  public function disconnect(): void
  {
    $this->client->disconnect();
  }

  public function isConnected(): bool
  {
    return $this->client->isConnected();
  }

  public function getClient(): Client
  {
    return $this->client;
  }

  public function getName(): string
  {
    return $this->name;
  }
}
