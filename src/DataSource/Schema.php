<?php

namespace Assegai\Orm\DataSource;

use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Exceptions\ClassNotFoundException;
use Assegai\Orm\Exceptions\GeneralSQLQueryException;
use Assegai\Orm\Exceptions\ORMException;
use Assegai\Orm\Management\EntityManager;
use Assegai\Orm\Interfaces\ISchema;
use PDO;
use PDOException;
use ReflectionClass;
use ReflectionException;

class Schema implements ISchema
{
  /**
   * @param string $entityClass
   * @param SchemaOptions|null $options
   * @return bool|null
   * @throws ORMException
   * @throws ClassNotFoundException
   */
  public static function create(string $entityClass, ?SchemaOptions $options = new SchemaOptions()): ?bool
  {
    if (!$options)
    {
      $options = new SchemaOptions();
    }

    try
    {
      EntityManager::validateEntityName(entityClass: $entityClass);

      $reflection = new ReflectionClass(objectOrClass: $entityClass);
      $instance = $reflection->newInstance();
      $db = DBFactory::getSQLConnection(dbName: $options->dbName(), dialect: $options->dialect());

      if ($options->dropSchema)
      {
        self::dropTableIfExists(entityClass: $entityClass, options: $options);
      }

      $query = $instance->schema(dialect: $options->dialect());
      $statement = $db->prepare(query: $query);

      return $statement->execute();
    }
    catch(ReflectionException $e)
    {
      throw new ORMException(message: $e->getMessage());
    }
  }

  /**
   * @param string $entityClass
   * @param SchemaOptions|null $options
   * @return bool|null
   * @throws ORMException
   */
  public static function createIfNotExists(string $entityClass, ?SchemaOptions $options = new SchemaOptions()): ?bool
  {
    if (!$options)
    {
      $options = new SchemaOptions();
    }

    try
    {
      $reflection = new ReflectionClass(objectOrClass: $entityClass);
      $instance = $reflection->newInstance();
      $query = $instance->schema(dialect: $options->dialect());

      $db = DBFactory::getSQLConnection(dbName: $options->dbName(), dialect: $options->dialect());
      $statement = $db->prepare(query: $query);

      return $statement->execute();
    }
    catch(ReflectionException $e)
    {
      throw new ORMException(message: $e->getMessage());
    }
  }

  /**
   * @inheritDoc
   * @param string $from
   * @param string $to
   * @param SchemaOptions|null $options
   * @return bool|null
   * @throws ORMException
   */
  public static function renameTable(string $from, string $to, ?SchemaOptions $options = new SchemaOptions()): ?bool
  {
    $query = "RENAME TABLE '$from' TO '$to'";

    $db = DBFactory::getSQLConnection(dbName: $options->dbName(), dialect: $options->dialect());
    $statement = $db->prepare(query: $query);

    try
    {
      $result = $statement->execute();
    }
    catch (PDOException)
    {
      throw new ORMException(message: $e->getMessage());
    }

    return $result;
  }

  /**
   * @inheritDoc
   */
  public static function alter(string $entityClass, ?SchemaOptions $options = new SchemaOptions()): ?bool
  {
    // TODO: Implement alter() method.
    return true;
  }

  /**
   * @inheritDoc
   */
  public static function info(string $entityClass, ?SchemaOptions $options = new SchemaOptions()): ?string
  {
    // TODO: Implement info() method.
    return true;
  }

  /**
   * @inheritDoc
   */
  public static function truncate(string $entityClass, ?SchemaOptions $options = new SchemaOptions()): ?bool
  {
    // TODO: Implement truncate() method.
    return true;
  }

  /**
   * @param string $entityClass
   * @param SchemaOptions|null $options
   * @return bool|null
   * @throws ORMException
   */
  public static function dropTable(string $entityClass, ?SchemaOptions $options = new SchemaOptions()): ?bool
  {
    if (!$options)
    {
      $options = new SchemaOptions();
    }

    try
    {
      $reflection = new ReflectionClass(objectOrClass: $entityClass);
      $instance = $reflection->newInstance();
      $tableName = $instance->tableName();
      $query = "DROP TABLE `$tableName`";

      $db = DBFactory::getSQLConnection(dbName: $options->dbName(), dialect: $options->dialect());
      $statement = $db->prepare(query: $query);

      return $statement->execute();
    }
    catch(ReflectionException $e)
    {
      throw new ORMException(message: $e->getMessage());
    }
  }

  /**
   * @param string $entityClass
   * @param SchemaOptions|null $options
   * @return bool|null
   * @throws ORMException
   */
  public static function dropTableIfExists(string $entityClass, ?SchemaOptions $options = new SchemaOptions()): ?bool
  {
    if (is_null($options))
    {
      $options = new SchemaOptions();
    }

    try
    {
      $reflection = new ReflectionClass(objectOrClass: $entityClass);
      $instance = $reflection->newInstance();
      $tableName = $instance->tableName();
      $query = "DROP TABLE";

      $query .= match ($options->dialect()) {
        default => ' IF EXISTS',
      };
      $query .= " `$tableName`";

      $db = DBFactory::getSQLConnection(dbName: $options->dbName(), dialect: $options->dialect());
      $statement = $db->prepare(query: $query);

      return $statement->execute();
    }
    catch(ReflectionException $e)
    {
      throw new ORMException(message: $e->getMessage());
    }
  }

  /**
   * @param PDO|DataSource $dataSource
   * @param string $databaseName
   * @return bool
   * @throws GeneralSQLQueryException
   */
  public static function dbExists(PDO|DataSource $dataSource, string $databaseName): bool
  {
    $query = "SHOW DATABASES LIKE '$databaseName'";
    $executionResult = ($dataSource instanceof DataSource)
      ? $dataSource->manager->query(query: $query)
      : $dataSource->query($query);

    if ($executionResult === false)
    {
      throw new GeneralSQLQueryException();
    }

    $result = $executionResult->fetchAll(PDO::FETCH_ASSOC);

    return !empty($result);
  }

  /**
   * @param PDO|DataSource $dataSource
   * @param string $databaseName
   * @param string $tableName
   * @param SQLDialect $dialect
   * @return bool
   * @throws GeneralSQLQueryException
   */
  public static function dbTableExists(PDO|DataSource $dataSource, string $databaseName, string $tableName, SQLDialect $dialect = SQLDialect::MYSQL): bool
  {
    $query = "SHOW TABLES LIKE '$tableName'";

    $executionResult = ($dataSource instanceof DataSource)
      ? $dataSource->manager->query(query: $query)
      : $dataSource->query($query);

    if ($executionResult === false)
    {
      throw new GeneralSQLQueryException();
    }

    $result = $executionResult->fetchAll(PDO::FETCH_ASSOC);

    return !empty($result);
  }
}