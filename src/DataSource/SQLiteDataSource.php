<?php

namespace Assegai\Orm\DataSource;

use Assegai\Core\Config;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Interfaces\DataSourceInterface;
use InvalidArgumentException;
use PDO;

/**
 * Class SQLiteDataSource. Represents a SQLite data source.
 *
 * @package Assegai\Orm\DataSource
 */
class SQLiteDataSource extends PDO implements DataSourceInterface
{
  protected DataSourceType $type = DataSourceType::SQLITE;

  /** @noinspection DuplicatedCode */
  public function __construct(protected string $name)
  {
    $databases = Config::get('databases');

    if (! isset($databases[$this->type->value]) || ! isset($databases[$this->type->value][$name]) ) {
      throw new InvalidArgumentException("Database $name not found.");
    }

    $path = $databases[$this->type->value][$name]['path'];
    $dsn = 'sqlite:' . $path;
    parent::__construct($dsn);
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