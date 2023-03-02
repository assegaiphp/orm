<?php

namespace Assegai\Orm\Management;

use Assegai\Core\Util\Debug\Log;
use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Exceptions\DataSourceException;
use \PDO;
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
    if ($dataSource->type === DataSourceType::MONGODB)
    {
      // TODO: handle mongodb server connection
    }
    else
    {
      $statement = match ($dataSource->type) {
        DataSourceType::MSSQL => "CREATE DATABASE $databaseName",
        default => "CREATE DATABASE IF NOT EXISTS $databaseName"
      };

      try
      {
        $result = $dataSource->db->exec($statement);

        if ($result === false)
        {
          throw new DataSourceException("Failed to created database: $databaseName" .
            PHP_EOL . print_r($dataSource->db->errorInfo(), true));
        }
      }
      catch (PDOException $exception)
      {
        throw new DataSourceException($exception->getMessage());
      }
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
    if ($dataSource->type === DataSourceType::MONGODB)
    {
      // TODO: handle mongodb server connection
    }
    else
    {
      try
      {
        $result = match ($dataSource->type) {
          DataSourceType::MSSQL => $dataSource->db->exec("DROP DATABASE $databaseName"),
          default => $dataSource->db->exec("DROP DATABASE IF EXISTS $databaseName")
        };

        if ($result === false)
        {
          throw new DataSourceException("Failed to created database: $databaseName" .
            PHP_EOL . print_r($dataSource->db->errorInfo(), true));
        }
      }
      catch (PDOException $exception)
      {
        throw new DataSourceException($exception->getMessage());
      }
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
    try
    {
      $query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?";
      $statement = $dataSource->db->prepare($query);
      $statement->execute([$databaseName]);
      $result = $statement->fetch(PDO::FETCH_ASSOC);

      return ($result !== false);
    }
    catch (PDOException $e)
    {
      if ($logErrors)
      {
        Log::error(__METHOD__, $e->getMessage());
      }

      return false;
    }
  }
}