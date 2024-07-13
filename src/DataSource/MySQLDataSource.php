<?php

namespace Assegai\Orm\DataSource;

use Assegai\Core\Config;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Interfaces\DataSourceInterface;
use InvalidArgumentException;
use PDO;

/**
 * Class MySQLDataSource. Represents a MySQL data source.
 *
 * @package Assegai\Orm\DataSource
 */
class MySQLDataSource extends PDO implements DataSourceInterface
{
  protected DataSourceType $type = DataSourceType::MYSQL;

  /** @noinspection DuplicatedCode */
  public function __construct(protected string $name)
  {
    $databases = Config::get('databases');

    if (! isset($databases[$this->type->value]) || ! isset($databases[$this->type->value][$name]) ) {
      throw new InvalidArgumentException("Database $name not found.");
    }

    $config = $databases[$this->type->value][$name];
    $host = $config['host'] ?? 'localhost';
    $user = $config['user'] ?? 'root';
    $password = $config['password'] ?? $config['pass'] ?? '';
    $port = $config['port'] ?? 3306;

    $dsn = "mysql:host=$host;port=$port;dbname=$name";
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