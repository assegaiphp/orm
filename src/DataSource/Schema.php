<?php

namespace Assegai\Orm\DataSource;

use Assegai\Orm\Attributes\Columns\Column;
use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Exceptions\ClassNotFoundException;
use Assegai\Orm\Exceptions\DataSourceConnectionException;
use Assegai\Orm\Exceptions\GeneralSQLQueryException;
use Assegai\Orm\Exceptions\NotImplementedException;
use Assegai\Orm\Exceptions\ORMException;
use Assegai\Orm\Interfaces\IDataObject;
use Assegai\Orm\Management\ColumnInspector;
use Assegai\Orm\Management\EntityInspector;
use Assegai\Orm\Management\EntityManager;
use Assegai\Orm\Interfaces\ISchema;
use Assegai\Orm\Metadata\SQLTableDescription;
use Assegai\Orm\Migrations\SchemaChangeManifest;
use Assegai\Orm\Queries\DDL\DDLAddStatement;
use Assegai\Orm\Queries\DDL\DDLChangeStatement;
use Assegai\Orm\Queries\DDL\DDLDropStatement;
use PDO;
use PDOException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

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
      $entityInstance = $reflection->newInstance();
      $db = DBFactory::getSQLConnection(dbName: $options->dbName, dialect: $options->dialect);

      if ($options->dropSchema)
      {
        self::dropIfExists(entityClass: $entityClass, options: $options);
      }

      $query = self::getDDLStatementFromEntity($entityInstance, $options);
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
      $entityInstance = $reflection->newInstance();
      $db = DBFactory::getSQLConnection(dbName: $options->dbName, dialect: $options->dialect);

      $query = self::getDDLStatementFromEntity($entityInstance, $options);
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
  public static function rename(string $from, string $to, ?SchemaOptions $options = new SchemaOptions()): ?bool
  {
    $dbName = $options ? ("`$options->dbName`." ?? '') : '';
    $query = "RENAME TABLE $dbName`$from` TO $dbName`$to`";

    $db = DBFactory::getSQLConnection(dbName: $options->dbName, dialect: $options->dialect);
    $statement = $db->prepare(query: $query);

    try
    {
      $result = $statement->execute();
    }
    catch (PDOException $e)
    {
      throw new ORMException(message: $e->getMessage());
    }

    return $result;
  }

  /**
   * Alters the schema of a given entity class.
   * @note To rename a column a migration is the preferred method. This method will drop the old column and add a new
   * column of the given name
   * @warning Changing a column name could lead to data loss.
   *
   * @param string $entityClass The name of the entity class to alter.
   * @param SchemaOptions|null $options An optional instance of SchemaOptions.
   * @return bool|null Returns true if the schema was successfully altered, false if it was not, and null if the
   * operation was not completed.
   * @throws ClassNotFoundException
   * @throws ORMException If the entity name is invalid or if an error occurs while altering the schema.
   * @throws ReflectionException
   */
  public static function alter(string $entityClass, ?SchemaOptions $options = new SchemaOptions()): ?bool
  {
    # Get the entity table
    $entityInspector = EntityInspector::getInstance();
    $entityInspector->validateEntityName($entityClass);

    $entityReflection = new ReflectionClass($entityClass);
    $entityInstance = $entityReflection->newInstance();

    $tableName = $entityInspector->getTableName($entityInstance);

    # Describe the current table schema
    $db = DBFactory::getSQLConnection(dbName: $options->dbName, dialect: $options->dialect);
    $statement = $db->query("DESCRIBE `$tableName`");

    if (false === $statement->execute())
    {
      throw new ORMException("Failed to execute 'DESCRIBE `$tableName`'" . PHP_EOL . print_r($statement->errorInfo(), true));
    }

    $tableFields = $statement->fetchAll(PDO::FETCH_CLASS, SQLTableDescription::class);

    if (empty($tableFields))
    {
      return null;
    }

    $changes = self::compileChanges($entityReflection, $tableFields);

    if (empty($changes))
    {
      return null;
    }

    return self::commitSchemaChanges($db, $changes);
  }

  /**
   * @inheritDoc
   */
  public static function info(string $entityClass, ?SchemaOptions $options = new SchemaOptions()): ?string
  {
    // TODO: Implement info() method.
    $result = false;
    return $result;
  }

  /**
   * @inheritDoc
   * @param string $entityClass
   * @param SchemaOptions|null $options
   * @return bool|null
   * @throws ClassNotFoundException when the given entity classname does not exist.
   * @throws ORMException when an error occurs while executing the SQL statement.
   * @throws ReflectionException when the given entity class is cannot be reflected.
   * @throws DataSourceConnectionException when an error occurs while establishing a connection to the database.
   */
  public static function truncate(string $entityClass, ?SchemaOptions $options = new SchemaOptions()): ?bool
  {
    $entityInspector = EntityInspector::getInstance();
    $entityInspector->validateEntityName($entityClass);
    $reflection = new ReflectionClass($entityClass);
    $entityInstance = $reflection->newInstance();
    $dbName = $options ? ("`$options->dbName`." ?? '') : '';
    $tableName = $entityInspector->getTableName($entityInstance);
    $query = "TRUNCATE TABLE $dbName`$tableName`";

    $db = DBFactory::getSQLConnection(dbName: $options->dbName, dialect: $options->dialect);
    $statement = $db->prepare(query: $query);

    try
    {
      $result = $statement->execute();
    }
    catch (PDOException $e)
    {
      throw new ORMException(message: $e->getMessage());
    }

    return $result;
  }

  /**
   * @param string $entityClass
   * @param SchemaOptions|null $options
   * @return bool|null
   * @throws ORMException
   */
  public static function drop(string $entityClass, ?SchemaOptions $options = new SchemaOptions()): ?bool
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
      $query = "DROP TABLE $tableName";

      $db = DBFactory::getSQLConnection(dbName: $options->dbName, dialect: $options->dialect);
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
  public static function dropIfExists(string $entityClass, ?SchemaOptions $options = new SchemaOptions()): ?bool
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

      $query .= match ($options->dialect) {
        SQLDialect::MYSQL,
        SQLDialect::MARIADB => ' IF EXISTS',
        default => '',
      };
      $query .= " `$tableName`";

      $db = DBFactory::getSQLConnection(dbName: $options->dbName, dialect: $options->dialect);
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
   * @param string $tableName
   * @param SQLDialect $dialect
   * @return bool
   * @throws GeneralSQLQueryException
   */
  public static function exists(string $tableName, DataSource $dataSource): bool
  {
    $query = "SHOW TABLES LIKE '$tableName'";

    $executionResult = $dataSource->manager->query(query: $query);

    if ($executionResult === false)
    {
      throw new GeneralSQLQueryException();
    }

    $result = $executionResult->fetchAll(PDO::FETCH_ASSOC);

    return !empty($result);
  }

  /**
   * @param string $tableName
   * @param string[] $columnNames
   * @return bool
   */
  public static function hasColumns(string $tableName, array $columnNames): bool
  {
    // TODO: Implement hasColumn() method.
    throw new NotImplementedException(__METHOD__);
  }

  /**
   * @param string|object $entityInstanceOrClassname
   * @param SchemaOptions $options
   * @return string
   * @throws ClassNotFoundException
   * @throws ORMException
   * @throws ReflectionException
   */
  protected static function getDDLStatementFromEntity(string|object $entityInstanceOrClassname, SchemaOptions $options): string
  {
    $reflection = new ReflectionClass($entityInstanceOrClassname);
    $entityInstance = $reflection->newInstance();
    $entityInspector = EntityInspector::getInstance();
    $tableName = $entityInspector->getTableName($entityInstance);

    $temporary = $options->isTemporary ? " TEMPORARY " : " ";
    $ifExists = $options->checkIfExists ? " IF NOT EXISTS " : " ";

    $entityPropertyReflection = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

    $createDefinitions = "";
    foreach ($entityPropertyReflection as $reflectionProperty)
    {
      $attributes = $reflectionProperty->getAttributes();
      $columnAttribute = array_find($attributes, function($attribute) {
        return (is_a($attribute->getName(), Column::class, true) ||
        is_subclass_of($attribute->getName(), Column::class));
      });

      if (!$columnAttribute)
      {
        continue;
      }

      /** @var ReflectionAttribute $columnAttribute */
      /** @var Column $columnAttributeInstance */
      $columnAttributeInstance = $columnAttribute->newInstance();
      if (!$columnAttributeInstance->name)
      {
        $createDefinitions .= "`" . $reflectionProperty->getName() . "` ";
      }
      $createDefinitions .= $columnAttributeInstance->sqlDefinition . ',' . PHP_EOL;
    }

    $createDefinitions = trim($createDefinitions, ",\t\n\r\0\x0B");

    return "CREATE{$temporary}TABLE$ifExists`$options->dbName`.`$tableName` " .
      "($createDefinitions) ENGINE=InnoDB " .
      "DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
  }

  /**
   * @param ReflectionClass $entityReflection
   * @param SQLTableDescription[] $currentTableFields
   * @return SchemaChangeManifest
   * @throws ClassNotFoundException
   * @throws ORMException
   * @throws ReflectionException
   */
  private static function compileChanges(ReflectionClass $entityReflection, array $currentTableFields): SchemaChangeManifest
  {
    # For each entity property, if it has a column attribute, compare column definitions with current table schema
    $columnInspector = ColumnInspector::getInstance();
    $entityInspector = EntityInspector::getInstance();
    $entityAttribute = $entityInspector->getMetaData($entityReflection->newInstance());

    $changes = new SchemaChangeManifest($entityAttribute);
    /** @var Column[] $entityColumnAttributes */
    $entityColumnAttributes = [];
    $entityFieldNames = [];
    $tableFieldNames = [];
    $tableFieldMap = [];
    foreach ($currentTableFields as $tableField)
    {
      $tableFieldNames[] = $tableField->Field;
      $tableFieldMap[$tableField->Field] = $tableField;
    }

    $propertyReflections = $entityReflection->getProperties(ReflectionProperty::IS_PUBLIC);

    foreach ($propertyReflections as $propertyReflection)
    {
      if ($columnInspector->propertyHasColumnAttribute($propertyReflection))
      {
        $columnAttribute = $columnInspector->getMetaDataFromReflection($propertyReflection);
        $columnName = $columnAttribute->name ?: $propertyReflection->getName();
        $entityColumnAttributes[$columnName] = $columnAttribute;
        $entityFieldNames[] = $columnName;
      }
    }

    // Compile list of columns to be added
    $columnsToAdd = array_diff($entityFieldNames, $tableFieldNames);
    foreach ($columnsToAdd as $columnName)
    {
      $column = $entityColumnAttributes[$columnName];
      $changes->add(new DDLAddStatement($columnName, $column->sqlDefinition));
    }

    // Compile list of columns to be dropped
    $columnsToDrop = array_diff($tableFieldNames, $entityFieldNames);
    foreach ($columnsToDrop as $columnName)
    {
      $changes->drop(new DDLDropStatement($columnName));
    }

    /* NOTE: The Field property refers to the actual column name stored in the database */
    // Compile list of columns to be modified/changed
    foreach ($entityColumnAttributes as $columnName => $columnAttribute)
    {
      $skippableColumns = array_merge($columnsToAdd, $columnsToDrop);
      if (in_array($columnName, $skippableColumns))
      {
        continue;
      }

      $tableField = $tableFieldMap[$columnName];
      if (self::shouldModifyField($tableField, $columnAttribute))
      {
        $changes->change(new DDLChangeStatement($tableField->Field, $columnAttribute->sqlDefinition));
      }
    }

    return $changes;
  }

  /**
   * @param IDataObject|PDO $connection
   * @param SchemaChangeManifest $changes
   * @return bool
   * @throws ORMException
   */
  private static function commitSchemaChanges(IDataObject|PDO $connection, SchemaChangeManifest $changes): bool
  {
    $query = (string)$changes;


    if (false === $connection->exec($query))
    {
      $errorMessage = json_encode($connection->errorInfo());
      throw new ORMException($errorMessage);
    }

    return true;
  }

  /**
   * @param SQLTableDescription $tableField
   * @param Column $columnAttribute
   * @return bool Returns true if the columnAttribute contains difference from the $tableField, false otherwise
   */
  private static function shouldModifyField(SQLTableDescription $tableField, Column $columnAttribute): bool
  {
    if ($tableField->Default !== $columnAttribute->default)
    {
      return true;
    }

    if (
      ($tableField->Key === 'PRI' && $columnAttribute->isPrimaryKey === false) ||
      ($tableField->Key === 'UNI' && $columnAttribute->isUnique === false)
    )
    {
      return true;
    }

    if (
      ($tableField->Null === 'YES' && $columnAttribute->nullable === false) ||
      ($tableField->Null === 'NO' && $columnAttribute->nullable === true)
    )
    {
      return true;
    }

    // TODO: Compare field type to column attribute type and lengthOrValues

    // TODO: Compare field extra to column attribute extra details

    return false;
  }
}