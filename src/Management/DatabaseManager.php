<?php

namespace Assegai\Orm\Management;

use Assegai\Orm\Support\OrmRuntime;
use Assegai\Orm\DataSource\DBFactory;
use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\DataSource\SQLCharacterSet;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Exceptions\DataSourceException;
use Assegai\Orm\Util\SqlDialectHelper;
use PDO;
use PDOException;

/**
 * The DatabaseManager class provides methods to check the existence, setup, drop, and reset a database.
 */
final class DatabaseManager
{
  private static ?DatabaseManager $instance = null;

  private function __construct()
  {
  }

  /**
   * Returns a singleton instance of DatabaseManager.
   *
   * @return static Returns a singleton instance of DatabaseManager.
   */
  public static function getInstance(): self
  {
    if (!self::$instance)
    {
      self::$instance = new DatabaseManager();
    }

    return self::$instance;
  }

  /**
   * Sets up a new database.
   *
   * @param DataSource $dataSource The data source to use.
   * @param string $databaseName The name of the database to create.
   * @return void
   * @throws DataSourceException If there was an error checking for the database.
   */
  public function setup(DataSource $dataSource, string $databaseName): void
  {
    if ($dataSource->type === DataSourceType::SQLITE)
    {
      $path = $this->resolveSqliteDatabasePath($dataSource, $databaseName);

      if ($this->isVirtualSqlitePath($path))
      {
        return;
      }

      $directory = dirname($path);
      if ($directory !== '' && $directory !== '.' && !is_dir($directory))
      {
        if (!@mkdir($directory, 0777, true) && !is_dir($directory))
        {
          throw new DataSourceException("Failed to create SQLite database directory: $directory");
        }
      }

      if (!file_exists($path) && false === @touch($path))
      {
        throw new DataSourceException("Failed to create SQLite database file: $path");
      }

      return;
    }

    if ($dataSource->type === DataSourceType::MONGODB)
    {
      // TODO: handle mongodb server connection
      return;
    }

    $statement = self::buildCreateDatabaseStatement(
      $dataSource->type,
      $databaseName,
      $dataSource->getOptions()->charSet
    );
    $client = $this->getManagementClient($dataSource, $databaseName);

    try
    {
      $result = $client->exec($statement);

      if ($result === false)
      {
        throw new DataSourceException("Failed to create database: $databaseName" .
          PHP_EOL . print_r($client->errorInfo(), true));
      }
    }
    catch (PDOException $exception)
    {
      throw new DataSourceException($exception->getMessage());
    }
    finally
    {
      $this->closeTemporaryManagementClient($dataSource, $client);
    }
  }

  /**
   * Drops an existing database.
   *
   * @param DataSource $dataSource The data source to use.
   * @param string $databaseName The name of the database to drop.
   * @return void
   * @throws DataSourceException If there was an error checking for the database.
   */
  public function drop(DataSource $dataSource, string $databaseName): void
  {
    if ($dataSource->type === DataSourceType::SQLITE)
    {
      $path = $this->resolveSqliteDatabasePath($dataSource, $databaseName);

      if ($this->isVirtualSqlitePath($path) || !file_exists($path))
      {
        return;
      }

      if (!@unlink($path))
      {
        throw new DataSourceException("Failed to drop SQLite database file: $path");
      }

      return;
    }

    if ($dataSource->type === DataSourceType::MONGODB)
    {
      // TODO: handle mongodb server connection
      return;
    }

    $client = $this->getManagementClient($dataSource, $databaseName);

    try
    {
      if ($dataSource->type === DataSourceType::POSTGRESQL)
      {
        $this->terminatePostgreSqlConnections($client, $databaseName);
      }

      $result = $client->exec(
        self::buildDropDatabaseStatement($dataSource->type, $databaseName)
      );

      if ($result === false)
      {
        throw new DataSourceException("Failed to drop database: $databaseName" .
          PHP_EOL . print_r($client->errorInfo(), true));
      }
    }
    catch (PDOException $exception)
    {
      throw new DataSourceException($exception->getMessage());
    }
    finally
    {
      $this->closeTemporaryManagementClient($dataSource, $client);
    }
  }

  /**
   * Drops and recreates a database.
   *
   * @param DataSource $dataSource The data source to reset.
   * @param string $databaseName The name of the database to reset.
   * @return void
   * @throws DataSourceException If there was an error dropping or recreating the database.
   */
  public function reset(DataSource $dataSource, string $databaseName): void
  {
    $this->drop($dataSource, $databaseName);
    $this->setup($dataSource, $databaseName);
  }

  /**
   * Checks if a database exists.
   *
   * @param DataSource $dataSource The data source to use.
   * @param string $databaseName The name of the database to check.
   * @return bool True if the database exists, false otherwise.
   */
  public function exists(DataSource $dataSource, string $databaseName, bool $logErrors = true): bool
  {
    $client = null;

    try
    {
      if ($dataSource->type === DataSourceType::SQLITE)
      {
        $path = $this->resolveSqliteDatabasePath($dataSource, $databaseName);

        if ($this->isVirtualSqlitePath($path))
        {
          return $dataSource->isConnected();
        }

        return file_exists($path);
      }

      $client = $this->getManagementClient($dataSource, $databaseName);

      $query = match ($dataSource->type) {
        DataSourceType::POSTGRESQL => 'SELECT datname FROM pg_database WHERE datname = ?',
        default => 'SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?',
      };

      $statement = $client->prepare($query);
      $statement->execute([$databaseName]);
      $result = $statement->fetch(PDO::FETCH_ASSOC);

      return ($result !== false);
    }
    catch (PDOException $e)
    {
      if ($logErrors)
      {
        OrmRuntime::log('error', __METHOD__, $e->getMessage());
      }

      return false;
    }
    finally
    {
      if ($client instanceof PDO)
      {
        $this->closeTemporaryManagementClient($dataSource, $client);
      }
    }
  }

  /**
   * @param DataSource $dataSource
   * @param string $databaseName
   * @return string
   * @throws DataSourceException
   */
  public static function buildCreateDatabaseStatement(
    DataSourceType $type,
    string $databaseName,
    ?SQLCharacterSet $charSet = SQLCharacterSet::UTF8MB4,
  ): string
  {
    $quotedDatabaseName = self::quoteDatabaseIdentifier($type, $databaseName);

    return match ($type) {
      DataSourceType::MSSQL => "CREATE DATABASE $quotedDatabaseName",
      DataSourceType::POSTGRESQL => "CREATE DATABASE $quotedDatabaseName",
      DataSourceType::MARIADB,
      DataSourceType::MYSQL => sprintf(
        'CREATE DATABASE IF NOT EXISTS %s DEFAULT CHARACTER SET %s COLLATE %s',
        $quotedDatabaseName,
        ($charSet ?? SQLCharacterSet::UTF8MB4)->value,
        ($charSet ?? SQLCharacterSet::UTF8MB4)->getDefaultCollation(),
      ),
      default => "CREATE DATABASE IF NOT EXISTS $quotedDatabaseName",
    };
  }

  public static function buildDropDatabaseStatement(DataSourceType $type, string $databaseName): string
  {
    $quotedDatabaseName = self::quoteDatabaseIdentifier($type, $databaseName);

    return match ($type) {
      DataSourceType::MSSQL,
      DataSourceType::POSTGRESQL => "DROP DATABASE IF EXISTS $quotedDatabaseName",
      default => "DROP DATABASE IF EXISTS $quotedDatabaseName",
    };
  }

  private function getManagementClient(DataSource $dataSource, string $databaseName): PDO
  {
    if ($dataSource->type !== DataSourceType::POSTGRESQL)
    {
      return $dataSource->getClient();
    }

    $options = $dataSource->getOptions();
    $maintenanceDatabase = $databaseName === 'postgres' ? 'template1' : 'postgres';
    $client = new PDO(
      DBFactory::buildPostgreSqlDsn($options->host, $options->port, $maintenanceDatabase),
      $options->username ?? 'postgres',
      $options->password ?? '',
    );
    DBFactory::applyConnectionAttributes($client, SqlDialectHelper::fromDataSourceType(DataSourceType::POSTGRESQL));

    return $client;
  }

  private function closeTemporaryManagementClient(DataSource $dataSource, ?PDO &$client): void
  {
    if ($dataSource->type === DataSourceType::POSTGRESQL)
    {
      $client = null;
    }
  }

  private function terminatePostgreSqlConnections(PDO $client, string $databaseName): void
  {
    $statement = $client->prepare(
      'SELECT pg_terminate_backend(pid) FROM pg_stat_activity WHERE datname = :database AND pid <> pg_backend_pid()'
    );
    $statement->execute(['database' => $databaseName]);
  }

  private static function quoteDatabaseIdentifier(DataSourceType $type, string $databaseName): string
  {
    if (!preg_match('/^[A-Za-z0-9_][A-Za-z0-9_-]*$/', $databaseName)) {
      throw new DataSourceException("Unsafe database name: $databaseName");
    }

    return match ($type) {
      DataSourceType::MSSQL => '[' . str_replace(']', ']]', $databaseName) . ']',
      DataSourceType::POSTGRESQL => '"' . str_replace('"', '""', $databaseName) . '"',
      default => '`' . str_replace('`', '``', $databaseName) . '`',
    };
  }

  private function resolveSqliteDatabasePath(DataSource $dataSource, string $databaseName): string
  {
    $candidate = $dataSource->getOptions()->path;

    if (is_string($candidate) && $candidate !== '')
    {
      return SqlDialectHelper::normalizeSqlitePath($candidate);
    }

    if ($this->looksLikeSqlitePath($databaseName))
    {
      return SqlDialectHelper::normalizeSqlitePath($databaseName);
    }

    $name = $dataSource->getName();
    return SqlDialectHelper::normalizeSqlitePath($name !== '' ? $name : $databaseName);
  }

  private function looksLikeSqlitePath(string $path): bool
  {
    return $path === ':memory:'
      || str_starts_with($path, 'file:')
      || str_contains($path, DIRECTORY_SEPARATOR)
      || str_contains($path, '/')
      || preg_match('/\.(sqlite|sqlite3|db)$/i', $path) === 1;
  }

  private function isVirtualSqlitePath(string $path): bool
  {
    return $path === ':memory:' || str_starts_with($path, 'file:');
  }
}
