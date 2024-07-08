<?php

namespace Assegai\Orm\DataSource;

use Assegai\Core\Config;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Exceptions\DataSourceConnectionException;
use Assegai\Util\Path;
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

    if (!isset(DBFactory::$connections[$type][$dbName]) || empty(DBFactory::$connections[$type][$dbName])) {
      self::validateDatabaseDetails(type: $type, dbName: $dbName);
      $config = Config::get('databases')[$type][$dbName];

      if (empty($config)) {
        # Attempt to get the first config we find
        $databases = Config::get('databases')[$type];

        if (!empty($databases)) {
          $config = array_pop($databases);
        }
      }

      try {
        $host = null;
        $port = null;
        $name = $dbName;
        $user = null;
        $password = null;
        extract($config);
        DBFactory::$connections[$type][$dbName] = new PDO(
          dsn: "mysql:host=$host;port=$port;dbname=$name",
          username: $user,
          password: $password
        );
      } catch (PDOException) {
        throw new DataSourceConnectionException();
      }
    }

    return DBFactory::$connections[$type][$dbName];
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

    if (!isset(DBFactory::$connections[$type][$dbName]) || empty(DBFactory::$connections[$type][$dbName])) {
      self::validateDatabaseDetails(type: $type, dbName: $dbName);
      $config = Config::get('databases')[$type][$dbName];

      try {
        $host = null;
        $port = null;
        $name = null;
        $user = null;
        $password = null;
        extract($config);
        DBFactory::$connections[$type][$dbName] = new PDO(
          dsn: "pgsql:host=$host;port=$port;dbname=$name",
          username: $user,
          password: $password
        );
      } catch (PDOException) {
        throw new DataSourceConnectionException(DataSourceType::POSTGRESQL);
      }
    }

    return DBFactory::$connections[$type][$dbName];
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

    if (!isset(DBFactory::$connections[$type][$dbName]) || empty(DBFactory::$connections[$type][$dbName])) {
      self::validateDatabaseDetails(type: $type, dbName: $dbName);
      $config = Config::get('databases')[$type][$dbName];

      try {
        $path = null;
        extract($config);
        $path = Path::join(getcwd() ?: '', $path);
        DBFactory::$connections[$type][$dbName] = new PDO( dsn: "sqlite:$path" );
      } catch (PDOException) {
        throw new DataSourceConnectionException(DataSourceType::SQLITE);
      }
    }

    return DBFactory::$connections[$type][$dbName];
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

    if (!isset(DBFactory::$connections[$type][$dbName]) || empty(DBFactory::$connections[$type][$dbName])) {
      self::validateDatabaseDetails(type: $type, dbName: $dbName);
      $config = Config::get('databases')[$type][$dbName];

      try {
        # TODO #16 Implement mongodb connection @amasiye
      } catch (PDOException) {
        die(new DataSourceConnectionException(DataSourceType::MONGODB));
      }
    }

    return DBFactory::$connections[$type][$dbName];
  }

  /**
   * @param string $type
   * @param string $dbName
   * @return void
   * @throws DataSourceConnectionException
   */
  private static function validateDatabaseDetails(string $type, string $dbName): void
  {
    $databases = Config::get('databases');

    if (!isset($databases[$type]) || !isset($databases[$type][$dbName])) {
      throw new DataSourceConnectionException();
    }
  }
}