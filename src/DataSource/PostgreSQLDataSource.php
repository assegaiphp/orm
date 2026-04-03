<?php

namespace Assegai\Orm\DataSource;

use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Interfaces\DataSourceInterface;
use Assegai\Orm\Support\OrmRuntime;
use InvalidArgumentException;
use PDO;

/**
 * Class PostgreSQLDataSource. Represents a PostgreSQL data source.
 *
 * @package Assegai\Orm\DataSource
 */
class PostgreSQLDataSource implements DataSourceInterface
{
  protected DataSourceType $type = DataSourceType::POSTGRESQL;
  protected bool $connected = true;
  protected PDO $client;

  /** @noinspection DuplicatedCode */
  public function __construct(protected string $name)
  {
    $databases = OrmRuntime::databaseConfigs();

    if (!isset($databases[$this->type->value]) || !isset($databases[$this->type->value][$name])) {
      throw new InvalidArgumentException("Database $name not found.");
    }

    $options = DataSourceOptions::fromArray([
      ...$databases[$this->type->value][$name],
      'name' => $name,
      'type' => $this->type,
    ]);
    $user = $options->username ?? 'postgres';
    $password = $options->password ?? '';

    $dsn = DBFactory::buildPostgreSqlDsn($options->host, $options->port, $options->name);
    $this->client = new PDO($dsn, $user, $password);
    DBFactory::applyConnectionAttributes($this->client, SQLDialect::POSTGRESQL);
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
