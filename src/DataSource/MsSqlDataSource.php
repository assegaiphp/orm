<?php

namespace Assegai\Orm\DataSource;

use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Interfaces\DataSourceInterface;
use Assegai\Orm\Support\OrmRuntime;
use InvalidArgumentException;
use PDO;

/**
 * Represents a SQL Server data source created from runtime configuration.
 */
class MsSqlDataSource implements DataSourceInterface
{
  protected DataSourceType $type = DataSourceType::MSSQL;
  protected bool $connected = true;
  protected PDO $client;

  /**
   * Create the SQL Server data source for the named runtime database.
   *
   * @param string $name The configured SQL Server database name.
   */
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
    $user = $options->username ?? 'sa';
    $password = $options->password ?? '';

    $dsn = DBFactory::buildMsSqlDsn($options->host, $options->port, $options->name);
    $this->client = new PDO($dsn, $user, $password);
    DBFactory::applyConnectionAttributes($this->client, SQLDialect::MSSQL);
  }

  /**
   * This legacy data source connects eagerly during construction.
   *
   * @param DataSourceOptions|array|null $options Unused for this legacy helper.
   * @return void
   */
  public function connect(DataSourceOptions|array|null $options): void
  {
    // Do nothing.
  }

  /**
   * Disconnect the SQL Server data source.
   *
   * @return void
   */
  public function disconnect(): void
  {
    if ($this->client->inTransaction()) {
      $this->client->rollBack();
    }

    $this->connected = false;
  }

  /**
   * Determine whether the legacy SQL Server data source is still connected.
   *
   * @return bool Returns true when the data source is still marked connected.
   */
  public function isConnected(): bool
  {
    return $this->connected;
  }

  /**
   * Retrieve the underlying SQL Server PDO client.
   *
   * @return PDO Returns the SQL Server PDO instance.
   */
  public function getClient(): PDO
  {
    return $this->client;
  }

  /**
   * Retrieve the configured SQL Server database name.
   *
   * @return string Returns the configured SQL Server database name.
   */
  public function getName(): string
  {
    return $this->name;
  }
}
