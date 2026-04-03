<?php

namespace Assegai\Orm\DataSource;

use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Enumerations\SQLDialect;
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
class SQLiteDataSource implements DataSourceInterface
{
  protected DataSourceType $type = DataSourceType::SQLITE;
  protected bool $connected = true;
  protected PDO $client;

  /** @noinspection DuplicatedCode */
  public function __construct(protected string $name)
  {
    $databases = OrmRuntime::databaseConfigs();

    if (!isset($databases[$this->type->value]) || !isset($databases[$this->type->value][$name])) {
      throw new InvalidArgumentException("Database $name not found.");
    }

    $path = SqlDialectHelper::normalizeSqlitePath($databases[$this->type->value][$name]['path']);
    $dsn = 'sqlite:' . $path;
    $this->client = new PDO($dsn);
    DBFactory::applyConnectionAttributes($this->client, SQLDialect::SQLITE);
  }

  public function connect(DataSourceOptions|array|null $options): void
  {
    // Do nothing.
  }

  public function disconnect(): void
  {
    if ($this->client->inTransaction()) {
      $this->client->rollBack();
    }

    $this->connected = false;
  }

  public function isConnected(): bool
  {
    return $this->connected;
  }

  public function getClient(): PDO
  {
    return $this->client;
  }

  public function getName(): string
  {
    return $this->name;
  }
}
