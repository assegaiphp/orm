<?php

namespace Assegai\Orm\DataSource;

use Assegai\Orm\Enumerations\SQLDialect;
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
   * @inheritDoc
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
      exit(new ORMException(message: $e->getMessage()));
    }
    catch (ORMException $e)
    {
      exit($e);
    }
  }

  /**
   * @inheritDoc
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
      exit(new ORMException(message: $e->getMessage()));
    }
    catch (ORMException $e)
    {
      exit($e);
    }
  }

  /**
   * @inheritDoc
   * @throws PDOException
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
      exit(new ORMException(message: $e->getMessage()));
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
   * @inheritDoc
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
      exit(new ORMException(message: $e->getMessage()));
    }
    catch (ORMException $e)
    {
      exit($e);
    }
  }

  /**
   * @inheritDoc
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
      exit(new ORMException(message: $e->getMessage()));
    }
    catch (ORMException $e)
    {
      exit($e);
    }
  }

  public static function dbExists(PDO|DataSource $dataSource, string $databaseName): bool
  {
    $query = "SHOW DATABASES LIKE '$databaseName'";
    $executionResult = ($dataSource instanceof DataSource)
      ? $dataSource->manager->query(query: $query)
      : $dataSource->query($query);

    if ($executionResult === false)
    {
      exit(new GeneralSQLQueryException());
    }

    $result = $executionResult->fetchAll(PDO::FETCH_ASSOC);

    return !empty($result);
  }

  public static function dbTableExists(PDO|DataSource $dataSource, string $databaseName, string $tableName, SQLDialect $dialect = SQLDialect::MYSQL): bool
  {
    $query = "SHOW TABLES LIKE '$tableName'";

    $executionResult = ($dataSource instanceof DataSource)
      ? $dataSource->manager->query(query: $query)
      : $dataSource->query($query);

    if ($executionResult === false)
    {
      exit(new GeneralSQLQueryException());
    }

    $result = $executionResult->fetchAll(PDO::FETCH_ASSOC);

    return !empty($result);
  }
}