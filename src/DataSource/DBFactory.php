<?php

namespace Assegai\Orm\DataSource;

use Assegai\Orm\Support\OrmRuntime;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Exceptions\DataSourceConnectionException;
use Assegai\Orm\Util\SqlDialectHelper;
use PDO;
use PDOException;

/**
 * The `DBFactory` class houses static methods for creating **Database
 * connection objects**.
 */
final class DBFactory
{
  /**
   * @var array|array[]
   */
  private static array $connections = [
    'mysql'   => [],
    'mariadb' => [],
    'pgsql'   => [],
    'sqlite'  => [],
    'mongodb' => [],
  ];

  /**
   * @var array<string, array<string, int>>
   */
  private static array $sharedConnectionReferences = [
    'mysql'   => [],
    'mariadb' => [],
    'pgsql'   => [],
    'sqlite'  => [],
    'mongodb' => [],
  ];

  /**
   * @param string $dbName
   * @param SQLDialect|null $dialect
   * @return PDO
   * @throws DataSourceConnectionException
   */
  public static function getSQLConnection(string $dbName, ?SQLDialect $dialect = SQLDialect::MYSQL): PDO
  {
    return match ($dialect) {
      SQLDialect::MARIADB     => self::getMariaDBConnection(dbName: $dbName),
      SQLDialect::POSTGRESQL  => self::getPostgresSQLConnection(dbName: $dbName),
      SQLDialect::SQLITE      => self::getSQLiteConnection(dbName: $dbName),
      default                 => self::getMySQLConnection(dbName: $dbName)
    };
  }

  /**
   * @param string $dbName
   * @return PDO
   * @throws DataSourceConnectionException
   */
  public static function getMySQLConnection(string $dbName): PDO
  {
    $type = 'mysql';

    if (empty($dbName)) {
      throw new DataSourceConnectionException();
    }

    if (!isset(self::$connections[$type][$dbName]) || empty(self::$connections[$type][$dbName])) {
      self::validateDatabaseDetails(type: $type, dbName: $dbName);
      $config = OrmRuntime::databaseConfigs()[$type][$dbName];

      if (empty($config)) {
        $databases = OrmRuntime::databaseConfigs()[$type];

        if (!empty($databases)) {
          $config = array_pop($databases);
        }
      }

      try {
        $options = DataSourceOptions::fromArray([
          ...$config,
          'name' => $config['name'] ?? $dbName,
          'database' => $config['database'] ?? $dbName,
          'type' => DataSourceType::MYSQL,
        ]);
        $user = $options->username ?? 'root';
        $password = $options->password ?? '';

        self::$connections[$type][$dbName] = new PDO(
          dsn: self::buildMySqlDsn($options->host, $options->port, $options->name, $options->charSet),
          username: $user,
          password: $password
        );
        self::applyConnectionAttributes(self::$connections[$type][$dbName], SQLDialect::MYSQL);
      } catch (PDOException) {
        throw new DataSourceConnectionException();
      }
    }

    return self::$connections[$type][$dbName];
  }

  /**
   * @param string $dbName
   * @return PDO
   * @throws DataSourceConnectionException
   */
  public static function getMariaDBConnection(string $dbName): PDO
  {
    return self::getMySQLConnection(dbName: $dbName);
  }

  /**
   * @param string $dbName
   * @return PDO
   * @throws DataSourceConnectionException
   */
  public static function getPostgresSQLConnection(string $dbName): PDO
  {
    $type = 'pgsql';

    if (empty($dbName)) {
      throw new DataSourceConnectionException();
    }

    if (!isset(self::$connections[$type][$dbName]) || empty(self::$connections[$type][$dbName])) {
      self::validateDatabaseDetails(type: $type, dbName: $dbName);
      $config = OrmRuntime::databaseConfigs()[$type][$dbName];

      try {
        $options = DataSourceOptions::fromArray([
          ...$config,
          'name' => $config['name'] ?? $dbName,
          'database' => $config['database'] ?? $dbName,
          'type' => DataSourceType::POSTGRESQL,
        ]);
        $user = $options->username ?? 'postgres';
        $password = $options->password ?? '';

        self::$connections[$type][$dbName] = new PDO(
          dsn: self::buildPostgreSqlDsn($options->host, $options->port, $options->name),
          username: $user,
          password: $password
        );
        self::applyConnectionAttributes(self::$connections[$type][$dbName], SQLDialect::POSTGRESQL);
      } catch (PDOException) {
        throw new DataSourceConnectionException(DataSourceType::POSTGRESQL);
      }
    }

    return self::$connections[$type][$dbName];
  }

  /**
   * @param string $dbName
   * @return PDO
   * @throws DataSourceConnectionException
   */
  public static function getSQLiteConnection(string $dbName): PDO
  {
    $type = 'sqlite';

    if (empty($dbName)) {
      throw new DataSourceConnectionException();
    }

    $config = OrmRuntime::databaseConfigs()[$type][$dbName] ?? null;

    try {
      $path = self::isDirectSqlitePath($dbName)
        ? $dbName
        : ($config['path'] ?? null);

      if (empty($path)) {
        throw new DataSourceConnectionException(DataSourceType::SQLITE);
      }

      $path = SqlDialectHelper::normalizeSqlitePath((string)$path);
      $cacheKey = self::getSqliteCacheKey($dbName, $path);

      if (!isset(self::$connections[$type][$cacheKey]) || empty(self::$connections[$type][$cacheKey])) {
        self::$connections[$type][$cacheKey] = new PDO(dsn: "sqlite:$path");
        self::applyConnectionAttributes(self::$connections[$type][$cacheKey], SQLDialect::SQLITE);
      }

      return self::$connections[$type][$cacheKey];
    } catch (PDOException) {
      throw new DataSourceConnectionException(DataSourceType::SQLITE);
    }
  }

  public static function retainSharedConnection(string $dbName, ?SQLDialect $dialect = SQLDialect::MYSQL): void
  {
    $type = self::getConnectionPoolType($dialect);
    $cacheKey = self::getConnectionCacheKey($dbName, $dialect);

    self::$sharedConnectionReferences[$type][$cacheKey] = (self::$sharedConnectionReferences[$type][$cacheKey] ?? 0) + 1;
  }

  public static function releaseSharedConnection(string $dbName, ?SQLDialect $dialect = SQLDialect::MYSQL): void
  {
    $type = self::getConnectionPoolType($dialect);
    $cacheKey = self::getConnectionCacheKey($dbName, $dialect);

    if (!isset(self::$sharedConnectionReferences[$type][$cacheKey])) {
      self::disconnectConnection($dbName, $dialect);
      return;
    }

    self::$sharedConnectionReferences[$type][$cacheKey]--;

    if (self::$sharedConnectionReferences[$type][$cacheKey] > 0) {
      return;
    }

    unset(self::$sharedConnectionReferences[$type][$cacheKey]);
    self::disconnectConnection($dbName, $dialect);
  }

  public static function disconnectConnection(string $dbName, ?SQLDialect $dialect = SQLDialect::MYSQL): void
  {
    $type = self::getConnectionPoolType($dialect);
    $cacheKey = self::getConnectionCacheKey($dbName, $dialect);
    $connection = self::$connections[$type][$cacheKey] ?? null;

    unset(self::$sharedConnectionReferences[$type][$cacheKey]);

    if ($connection instanceof PDO && $connection->inTransaction()) {
      $connection->rollBack();
    }

    self::$connections[$type][$cacheKey] = null;
    unset(self::$connections[$type][$cacheKey]);
  }

  /**
   * @param string $dbName
   * @return PDO
   * @throws DataSourceConnectionException
   */
  public static function getMongoDbConnection(string $dbName): PDO
  {
    $type = 'mongodb';

    if (empty($dbName)) {
      throw new DataSourceConnectionException();
    }

    if (!isset(self::$connections[$type][$dbName]) || empty(self::$connections[$type][$dbName])) {
      self::validateDatabaseDetails(type: $type, dbName: $dbName);
      $config = OrmRuntime::databaseConfigs()[$type][$dbName];

      try {
        # TODO #16 Implement mongodb connection @amasiye
      } catch (PDOException) {
        die(new DataSourceConnectionException(DataSourceType::MONGODB));
      }
    }

    return self::$connections[$type][$dbName];
  }

  /**
   * @param string $type
   * @param string $dbName
   * @return void
   * @throws DataSourceConnectionException
   */
  private static function validateDatabaseDetails(string $type, string $dbName): void
  {
    $databases = OrmRuntime::databaseConfigs();

    if (!isset($databases[$type]) || !isset($databases[$type][$dbName])) {
      throw new DataSourceConnectionException();
    }
  }

  public static function buildMySqlDsn(
    string $host,
    int $port,
    string $database,
    ?SQLCharacterSet $charSet = SQLCharacterSet::UTF8MB4
  ): string
  {
    $segments = [
      sprintf('mysql:host=%s', $host),
      sprintf('port=%d', $port),
      sprintf('dbname=%s', $database),
    ];

    if ($charSet instanceof SQLCharacterSet) {
      $segments[] = sprintf('charset=%s', $charSet->value);
    }

    return implode(';', $segments);
  }

  public static function buildPostgreSqlDsn(string $host, int $port, string $database): string
  {
    return sprintf('pgsql:host=%s;port=%d;dbname=%s', $host, $port, $database);
  }

  public static function getDefaultPdoAttributes(SQLDialect $dialect): array
  {
    $attributes = [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_STRINGIFY_FETCHES => false,
    ];

    if (in_array($dialect, [SQLDialect::MYSQL, SQLDialect::MARIADB], true)) {
      $attributes[PDO::ATTR_EMULATE_PREPARES] = false;
    }

    return $attributes;
  }

  public static function applyConnectionAttributes(PDO $connection, SQLDialect $dialect): void
  {
    foreach (self::getDefaultPdoAttributes($dialect) as $attribute => $value) {
      $connection->setAttribute($attribute, $value);
    }

    if ($dialect === SQLDialect::SQLITE) {
      $connection->exec('PRAGMA foreign_keys = ON');
      $connection->exec('PRAGMA busy_timeout = 5000');

      if (self::shouldEnableSqliteWal($connection)) {
        $connection->exec('PRAGMA journal_mode = WAL');
        $connection->exec('PRAGMA synchronous = NORMAL');
      }
    }
  }

  private static function shouldEnableSqliteWal(PDO $connection): bool
  {
    $databasePath = self::getPrimarySqliteDatabasePath($connection);

    return is_string($databasePath)
      && $databasePath !== ''
      && $databasePath !== ':memory:';
  }

  private static function getPrimarySqliteDatabasePath(PDO $connection): ?string
  {
    $statement = $connection->query('PRAGMA database_list');

    if ($statement === false) {
      return null;
    }

    $database = $statement->fetch(PDO::FETCH_ASSOC);
    $statement->closeCursor();

    if (!is_array($database)) {
      return null;
    }

    $path = $database['file'] ?? null;

    return is_string($path) ? $path : null;
  }

  private static function getConnectionPoolType(?SQLDialect $dialect): string
  {
    return match ($dialect) {
      SQLDialect::MARIADB => 'mysql',
      SQLDialect::POSTGRESQL => 'pgsql',
      SQLDialect::SQLITE => 'sqlite',
      default => 'mysql',
    };
  }

  private static function getConnectionCacheKey(string $dbName, ?SQLDialect $dialect): string
  {
    return $dialect === SQLDialect::SQLITE
      ? self::getSqliteCacheKey($dbName)
      : $dbName;
  }

  private static function isDirectSqlitePath(string $path): bool
  {
    return $path === ':memory:'
      || str_starts_with($path, 'file:')
      || str_contains($path, DIRECTORY_SEPARATOR)
      || str_contains($path, '/')
      || preg_match('/\.(sqlite|sqlite3|db)$/i', $path) === 1;
  }

  private static function getSqliteCacheKey(string $dbName, ?string $path = null): string
  {
    $resolvedPath = $path
      ?? OrmRuntime::databaseConfigs()['sqlite'][$dbName]['path']
      ?? $dbName;

    if (!self::isDirectSqlitePath($resolvedPath)) {
      return $dbName;
    }

    return SqlDialectHelper::normalizeSqlitePath((string)$resolvedPath);
  }
}
