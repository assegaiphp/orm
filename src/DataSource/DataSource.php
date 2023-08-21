<?php

namespace Assegai\Orm\DataSource;

use Assegai\Core\Config;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Exceptions\ClassNotFoundException;
use Assegai\Orm\Exceptions\DataSourceException;
use Assegai\Orm\Exceptions\IllegalTypeException;
use Assegai\Orm\Interfaces\DataSourceInterface;
use Assegai\Orm\Interfaces\IRepository;
use Assegai\Orm\Management\EntityManager;
use Assegai\Orm\Management\Repository;
use Assegai\Orm\Queries\Sql\SQLQuery;
use JetBrains\PhpStorm\ArrayShape;
use PDO;
use ReflectionClass;
use ReflectionException;

/**
 * Class DataSource. Represents a data source.
 *
 * @package Assegai\Orm\DataSource
 */
class DataSource implements DataSourceInterface
{
  public readonly EntityManager $manager;
  protected ?PDO $db = null;
  public readonly DataSourceType $type;
  public readonly array $entities;

  /**
   * @throws DataSourceException
   */
  #[ArrayShape([
    'entities' => 'array',
    'database' => 'string',
    'type' => 'Assegai\Orm\Enumerations\DataSourceType',
    'host' => 'string',
    'port' => 'int',
    'username' => 'string|null',
    'password' => 'string|null'
  ])]
  public function __construct(protected DataSourceOptions|array|null $options = null)
  {
    $this->connect($options);
  }

  /**
   * @param string $entityName The target entity for the repository
   * @return IRepository
   * @throws ClassNotFoundException
   * @throws IllegalTypeException
   * @throws ReflectionException
   */
  public function getRepository(string $entityName): IRepository
  {
    if (!class_exists($entityName))
    {
      throw new ClassNotFoundException(className: $entityName);
    }

    return new Repository(entityId: $entityName, manager: $this->manager);
  }

  /**
   * Retrieves the name of the current database.
   *
   * @return string|null The name of the current database, or null if it cannot be determined.
   */
  public function getDatabaseName(): ?string
  {
    // Execute a SQL query to retrieve the current database name.
    $databaseName = $this->db->query('SELECT DATABASE()')->fetchColumn();

    // If the query fails, return null to indicate that the database name cannot be determined.
    if (false === $databaseName)
    {
      return null;
    }

    // Return the database name as a string.
    return (string)$databaseName;
  }

  /**
   * @inheritDoc
   */
  public function getName(): string
  {
    return $this->options->name;
  }

  /**
   * @inheritDoc
   */
  public function connect(DataSourceOptions|array|null $options): void
  {
    $reflectionClass = new ReflectionClass($this);
    $refAttributes = $reflectionClass->getAttributes(DataSourceOptions::class);

    if (empty($options) && empty($refAttributes))
    {
      throw new DataSourceException("DataSourceOptions not set");
    }

    if (is_array($options))
    {
      $options = (object)$options;
    }

    $this->type = $options->type;

    if ($options->name && $options->type)
    {
      $type = $options->type->value;
      $databaseConfigs = Config::get('databases') ?? [];

      $databases = $databaseConfigs[$type] ?? [];
      $databaseConfig = $databases[$options->name];

      if (isset($databaseConfig['user']))
      {
        $databaseConfig['username'] = $databaseConfig['user'];
        unset($databaseConfig['user']);
      }

      if ($databaseConfig)
      {
        $options = new DataSourceOptions(...[
          ...$databaseConfig,
          'entities' => $options->entities ?? [],
          'type' => $options->type,
          'name' => $options->name,
          'synchronize' => $options->synchronize ?? false
        ]);
      }
    }

    if (
      !empty($options->name) &&
      !empty($options->username) &&
      !empty($options->password) &&
      !empty($options->port)
    )
    {
      $host = $options->host;
      $name = $options->name;
      $port = $options->port;

      $dsn = match ($this->type) {
        DataSourceType::POSTGRESQL => "pgsql:host=$host;port=$port;dbname=$name",
        DataSourceType::MSSQL => "sqlsrv:Server=$host,port;Database=$name",
        DataSourceType::SQLITE => "sqlite:$name",
        default => "mysql:host=$host;port=$port;dbname=$name"
      };

      $this->db = new PDO(dsn: $dsn, username: $options->username, password: $options->password);
    }
    else
    {
      $this->db = match ($this->type) {
        DataSourceType::POSTGRESQL  => DBFactory::getPostgresSQLConnection(dbName: $options->name),
        DataSourceType::SQLITE      => DBFactory::getSQLiteConnection(dbName: $options->name),
        DataSourceType::MONGODB     => DBFactory::getMongoDbConnection(dbName: $options->name),
        DataSourceType::MARIADB,
        DataSourceType::MYSQL       => DBFactory::getMySQLConnection(dbName: $options->name),
        default                     => DBFactory::getSQLConnection(dbName: $options->name)
      };
    }

    $this->manager = isset($options->entities) && count($options->entities) === 1
      ? new EntityManager(
        connection: $this,
        query: new SQLQuery(db: $this->db,fetchClass: $options->entities[0]::class, fetchMode: PDO::FETCH_CLASS)
      )
      : new EntityManager(connection: $this);
  }

  /**
   * @inheritDoc
   */
  public function disconnect(): void
  {
    $this->db = null;
  }

  /**
   * @inheritDoc
   */
  public function isConnected(): bool
  {
    return isset($this->db);
  }

  /**
   * @inheritDoc
   */
  public function getClient(): PDO
  {
    return $this->db;
  }
}