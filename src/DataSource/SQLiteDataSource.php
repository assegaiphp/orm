<?php

namespace Assegai\Orm\DataSource;

use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Interfaces\DataSourceInterface;
use Assegai\Orm\Support\OrmRuntime;
use Assegai\Orm\Util\SqlDialectHelper;
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
    $databases = OrmRuntime::databaseConfigs();

    if (!isset($databases[$this->type->value]) || !isset($databases[$this->type->value][$name])) {
      throw new InvalidArgumentException("Database $name not found.");
    }

    $path = SqlDialectHelper::normalizeSqlitePath($databases[$this->type->value][$name]['path']);
    $dsn = 'sqlite:' . $path;
    parent::__construct($dsn);
    $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $this->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $this->exec('PRAGMA foreign_keys = ON');
  }

  public function connect(DataSourceOptions|array|null $options): void
  {
    // Do nothing.
  }

  public function disconnect(): void
  {
    // Do nothing.
  }

  public function isConnected(): bool
  {
    return true;
  }

  public function getClient(): static
  {
    return $this;
  }

  public function getName(): string
  {
    return $this->name;
  }
}
