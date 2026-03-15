<?php

namespace Assegai\Orm\DataSource;

use Assegai\Core\Config;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Exceptions\ClassNotFoundException;
use Assegai\Orm\Exceptions\DataSourceConnectionException;
use Assegai\Orm\Exceptions\DataSourceException;
use Assegai\Orm\Exceptions\IllegalTypeException;
use Assegai\Orm\Exceptions\ORMException;
use Assegai\Orm\Interfaces\DataSourceInterface;
use Assegai\Orm\Interfaces\RepositoryInterface;
use Assegai\Orm\Management\EntityManager;
use Assegai\Orm\Management\Repository;
use Assegai\Orm\Queries\Sql\SQLQuery;
use Assegai\Orm\Util\SqlDialectHelper;
use PDO;
use PDOException;
use ReflectionClass;
use ReflectionException;

/**
 * Class DataSource. Represents a data source.
 *
 * @package Assegai\Orm\DataSource
 */
class DataSource implements DataSourceInterface
{
  /**
   * @var EntityManager The entity manager.
   */
  public readonly EntityManager $manager;
  /**
   * @var PDO|null The database connection.
   */
  protected ?PDO $connection = null;
  /**
   * @var DataSourceType The data source type.
   */
  public readonly DataSourceType $type;
  /**
   * @var array<class-string> The entities.
   */
  public readonly array $entities;

  /**
   * Constructs a DataSource.
   *
   * @param DataSourceOptions|array{
   *   entities: array<int|string, class-string|object>,
   *   database: string,
   *   type: DataSourceType,
   *   host: string,
   *   port: int,
   *   username: string|null,
   *   password: string|null
   * }|null $options The data source options.
   * @throws DataSourceException
   */
  public function __construct(protected DataSourceOptions|array|null $options = null)
  {
    $this->connect($options);
  }

  /**
   * Gets a repository for the specified entity.
   *
   * @param class-string $entityName The target entity for the repository
   * @return RepositoryInterface The repository for the specified entity
   * @throws ClassNotFoundException
   * @throws IllegalTypeException
   * @throws ReflectionException
   */
  public function getRepository(string $entityName): RepositoryInterface
  {
    if (!class_exists($entityName)) {
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
    $query = match ($this->type) {
      DataSourceType::POSTGRESQL => 'SELECT current_database()',
      DataSourceType::SQLITE => null,
      default => 'SELECT DATABASE()',
    };

    if (is_null($query)) {
      return $this->options->name ?? null;
    }

    $databaseName = $this->connection?->query($query)?->fetchColumn();

    if ($databaseName === false) {
      return null;
    }

    return $databaseName ? (string)$databaseName : null;
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
   * @param DataSourceOptions|array|null $options
   * @throws DataSourceException
   * @throws ORMException
   * @throws ReflectionException
   * @throws DataSourceConnectionException
   */
  public function connect(DataSourceOptions|array|null $options): void
  {
    $reflectionClass = new ReflectionClass($this);
    $refAttributes = $reflectionClass->getAttributes(DataSourceOptions::class);

    if (empty($options) && empty($refAttributes)) {
      throw new DataSourceException("DataSourceOptions not set");
    }

    if (is_array($options)) {
      $options = DataSourceOptions::fromArray($options);
    }

    if (!$options instanceof DataSourceOptions) {
      throw new DataSourceException("Invalid data source options.");
    }

    $options = $this->resolveOptions($options);
    $this->options = $options;
    $this->type = $options->type;
    $this->entities = array_map(
      fn(string|object $entity): string => $this->normalizeEntityClass($entity),
      $options->entities ?? []
    );

    try {
      $this->connection = $this->createConnection($options);
      $this->configureConnection($this->connection, SqlDialectHelper::fromDataSourceType($this->type));
    } catch (PDOException) {
      throw new DataSourceConnectionException($this->type);
    }

    $this->manager = isset($options->entities) && count($options->entities) === 1
      ? new EntityManager(
        connection: $this,
        query: new SQLQuery(db: $this->connection, fetchClass: $this->entities[0], fetchMode: PDO::FETCH_CLASS)
      )
      : new EntityManager(connection: $this);
  }

  /**
   * @inheritDoc
   */
  public function disconnect(): void
  {
    $this->connection = null;
  }

  /**
   * @inheritDoc
   */
  public function isConnected(): bool
  {
    return isset($this->connection);
  }

  /**
   * @inheritDoc
   */
  public function getClient(): PDO
  {
    return $this->connection;
  }

  public function getDialect(): SQLDialect
  {
    return SqlDialectHelper::fromDataSourceType($this->type);
  }

  /**
   * @throws ORMException
   */
  private function resolveOptions(DataSourceOptions $options): DataSourceOptions
  {
    $databaseConfigs = Config::get('databases') ?? [];
    $type = $options->type->value;
    $databaseConfig = $databaseConfigs[$type][$options->name] ?? null;

    if (empty($databaseConfig)) {
      return $options;
    }

    return DataSourceOptions::fromArray([
      ...$databaseConfig,
      'entities' => $options->entities,
      'name' => $options->name,
      'database' => $options->name,
      'type' => $options->type,
      'synchronize' => $options->synchronize,
      'path' => $options->path ?? $databaseConfig['path'] ?? null,
    ]);
  }

  /**
   * @throws DataSourceConnectionException
   */
  private function createConnection(DataSourceOptions $options): PDO
  {
    return match ($this->type) {
      DataSourceType::POSTGRESQL => $this->createPostgreSqlConnection($options),
      DataSourceType::SQLITE => $this->createSqliteConnection($options),
      DataSourceType::MONGODB => DBFactory::getMongoDbConnection(dbName: $options->name),
      DataSourceType::MARIADB,
      DataSourceType::MYSQL => $this->createMySqlConnection($options),
      DataSourceType::REDIS => DataSourceFactory::create($this->type, $options->name)->getClient(),
      default => DBFactory::getSQLConnection(dbName: $options->name),
    };
  }

  /**
   * @throws DataSourceConnectionException
   */
  private function createMySqlConnection(DataSourceOptions $options): PDO
  {
    if (empty($options->username) && empty($options->password)) {
      return DBFactory::getMySQLConnection(dbName: $options->name);
    }

    $dsn = "mysql:host={$options->host};port={$options->port};dbname={$options->name}";
    return new PDO(dsn: $dsn, username: $options->username, password: $options->password);
  }

  /**
   * @throws DataSourceConnectionException
   */
  private function createPostgreSqlConnection(DataSourceOptions $options): PDO
  {
    if (empty($options->username) && empty($options->password)) {
      return DBFactory::getPostgresSQLConnection(dbName: $options->name);
    }

    $dsn = "pgsql:host={$options->host};port={$options->port};dbname={$options->name}";
    return new PDO(dsn: $dsn, username: $options->username, password: $options->password);
  }

  /**
   * @throws DataSourceConnectionException
   */
  private function createSqliteConnection(DataSourceOptions $options): PDO
  {
    $path = $options->path ?? $options->name;

    if ($this->isDirectSqlitePath($path)) {
      $dsn = "sqlite:" . SqlDialectHelper::normalizeSqlitePath($path);
      return new PDO($dsn);
    }

    return DBFactory::getSQLiteConnection(dbName: $options->name);
  }

  private function configureConnection(PDO $connection, SQLDialect $dialect): void
  {
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    if ($dialect === SQLDialect::SQLITE) {
      $connection->exec('PRAGMA foreign_keys = ON');
    }
  }

  private function isDirectSqlitePath(string $path): bool
  {
    return $path === ':memory:'
      || str_starts_with($path, 'file:')
      || str_contains($path, DIRECTORY_SEPARATOR)
      || str_contains($path, '/')
      || preg_match('/\.(sqlite|sqlite3|db)$/i', $path) === 1;
  }

  /**
   * @param class-string|object $entity
   * @return class-string
   * @throws DataSourceException
   */
  private function normalizeEntityClass(string|object $entity): string
  {
    if (is_object($entity)) {
      return $entity::class;
    }

    if (class_exists($entity)) {
      return $entity;
    }

    throw new DataSourceException("Invalid entity reference provided to the data source.");
  }
}
