<?php

namespace Assegai\Orm\DataSource;

use Assegai\Core\Config;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Interfaces\DataSourceInterface;
use InvalidArgumentException;
use Predis\Client;

/**
 * RedisDataSource class.
 *
 * This class is responsible for managing the connection to a Redis data source.
 */
class RedisDataSource implements DataSourceInterface
{
  /**
   * Default values for Redis connection parameters.
   */
  const DEFAULT_SCHEME = 'tcp';
  const DEFAULT_HOST = '127.0.0.1';
  const DEFAULT_PORT = 6379;
  const DEFAULT_DATABASE = 0;
  /**
   * @var DataSourceType $type
   */
  protected DataSourceType $type = DataSourceType::REDIS;
  /**
   * @var Client $client
   */
  protected Client $client;

  /**
   * @param string $name The name of the Redis data source.
   */
  public function __construct(
    protected string $name,
  )
  {
    $databases = Config::get('databases');

    if (! isset($databases[$this->type->value]) || ! isset($databases[$this->type->value][$name]) ) {
      throw new InvalidArgumentException("Database $name not found.");
    }

    $config = $databases[$this->type->value][$name];
    extract($config);
    $scheme     ??= self::DEFAULT_SCHEME;
    $host       ??= self::DEFAULT_HOST;
    $port       ??= self::DEFAULT_PORT;
    $password   ??= '';
    $database   ??= self::DEFAULT_DATABASE;

    $this->client = new Client([
      'scheme'   => $scheme,
      'host'     => $host,
      'port'     => $port,
      'password' => $password,
      'database' => $database
    ]);
  }

  /**
   * @inheritDoc
   */
  public function connect(DataSourceOptions|array|null $options): void
  {
    $this->client->connect();
  }

  /**
   * @inheritDoc
   */
  public function disconnect(): void
  {
    $this->client->disconnect();
  }

  /**
   * @inheritDoc
   */
  public function isConnected(): bool
  {
    return $this->client->isConnected();
  }

  /**
   * @inheritDoc
   */
  public function getClient(): Client
  {
    return $this->client;
  }

  /**
   * @inheritDoc
   */
  public function getName(): string
  {
    return $this->name;
  }
}