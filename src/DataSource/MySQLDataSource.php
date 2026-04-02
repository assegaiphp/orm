<?php

namespace Assegai\Orm\DataSource;

use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Interfaces\DataSourceInterface;
use Assegai\Orm\Support\OrmRuntime;
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
    $databases = OrmRuntime::databaseConfigs();

    if (!isset($databases[$this->type->value]) || !isset($databases[$this->type->value][$name])) {
      throw new InvalidArgumentException("Database $name not found.");
    }

    $options = DataSourceOptions::fromArray([
      ...$databases[$this->type->value][$name],
      'name' => $name,
      'type' => $this->type,
    ]);
    $user = $options->username ?? 'root';
    $password = $options->password ?? '';

    $dsn = DBFactory::buildMySqlDsn($options->host, $options->port, $options->name, $options->charSet);
    parent::__construct($dsn, $user, $password);
    DBFactory::applyConnectionAttributes($this, SQLDialect::MYSQL);
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
