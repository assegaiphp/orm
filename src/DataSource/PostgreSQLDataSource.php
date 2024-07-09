<?php

namespace Assegai\Orm\DataSource;

use Assegai\Core\Config;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Interfaces\DataSourceInterface;
use InvalidArgumentException;
use PDO;

/**
 * Class PostgreSQLDataSource. Represents a PostgreSQL data source.
 *
 * @package Assegai\Orm\DataSource
 */
class PostgreSQLDataSource extends PDO implements DataSourceInterface
{
  protected DataSourceType $type = DataSourceType::POSTGRESQL;

  /** @noinspection DuplicatedCode */
  public function __construct(protected string $name)
  {
    $databases = Config::get('databases');

    if (! isset($databases[$this->type->value]) || ! isset($databases[$this->type->value][$name]) ) {
      throw new InvalidArgumentException("Database $name not found.");
    }

    $config = $databases[$this->type->value][$name];
    $host = $config['host'] ?? 'localhost';
    $user = $config['user'] ?? 'postgres';
    $password = $config['password'] ?? $config['pass'] ?? '';
    $port = $config['port'] ?? 5432;

    $dsn = "pgsql:host=$host;port=$port;dbname=$name";
    parent::__construct($dsn, $user, $password);
  }

  /**
   * @inheritDoc
   */
  public function connect(DataSourceOptions|array|null $options): void
  {
    // Do nothing.
  }

  /**
   * @inheritDoc
   */
  public function disconnect(): void
  {
    // Do nothing.
  }

  /**
   * @inheritDoc
   */
  public function isConnected(): bool
  {
    return true;
  }

  /**
   * @inheritDoc
   */
  public function getClient(): static
  {
    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getName(): string
  {
    return $this->name;
  }
}