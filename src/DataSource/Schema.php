<?php

namespace Assegai\Orm\DataSource;

use Assegai\Orm\Attributes\Columns\Column;
use Assegai\Orm\Enumerations\DataSourceType;
use Assegai\Orm\Enumerations\SQLDialect;
use Assegai\Orm\Exceptions\ClassNotFoundException;
use Assegai\Orm\Exceptions\DataSourceConnectionException;
use Assegai\Orm\Exceptions\GeneralSQLQueryException;
use Assegai\Orm\Exceptions\ORMException;
use Assegai\Orm\Interfaces\IDataObject;
use Assegai\Orm\Interfaces\ISchema;
use Assegai\Orm\Management\EntityManager;
use Assegai\Orm\Management\Inspectors\ColumnInspector;
use Assegai\Orm\Management\Inspectors\EntityInspector;
use Assegai\Orm\Metadata\SchemaMetadata;
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

    $db = DBFactory::getSQLConnection(dbName: $options->dbName, dialect: $options->dialect);
    $tableFields = self::getTableDescriptions($entityInstance, $db);

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
   * @throws ClassNotFoundException
   * @throws ORMException
   * @throws ReflectionException
   */
  public static function info(string $entityClass, ?SchemaOptions $options = new SchemaOptions()): ?SchemaMetadata
  {
    $entityInstance = self::getEntityInstance($entityClass);
    $entityInspector = EntityInspector::getInstance();

    $tableName = $entityInspector->getTableName($entityInstance);

    # Describe the current table schema
    $db = DBFactory::getSQLConnection(dbName: $options->dbName, dialect: $options->dialect);
    $tableFields = self::getTableDescriptions($entityInstance, $db);

    if (empty($tableFields))
    {
      return null;
    }

    $statement = $db->query("SHOW CREATE TABLE `$tableName`");

    if (false === $statement->execute())
    {
      throw new ORMException("Failed to execute 'SHOW CREATE TABLE `$tableName`'" . PHP_EOL . print_r($statement->errorInfo(), true));
    }

    $result = $statement->fetchColumn(1);

    if (!is_string($result))
    {
      if (!empty($statement->errorInfo()))
      {
        $message = print_r($statement->errorInfo(), true);
      }
      else
      {
        $message = "Invalid DDL statement for table `$tableName`";
      }

      throw new ORMException($message);
    }

    return new SchemaMetadata(tableFields: $tableFields, ddlStatement: $result);
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
      $entityInspector = EntityInspector::getInstance();
      $reflection = new ReflectionClass(objectOrClass: $entityClass);
      $entityInstance = $reflection->newInstance();
      $dbName = $options ? ("`$options->dbName`." ?? '') : '';
      $tableName = $entityInspector->getTableName($entityInstance);
      $query = "DROP TABLE $dbName`$tableName`";

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
      $entityInspector = EntityInspector::getInstance();
      $reflection = new ReflectionClass(objectOrClass: $entityClass);
      $entityInstance = $reflection->newInstance();
      $dbName = $options ? ("`$options->dbName`." ?? '') : '';
      $tableName = $entityInspector->getTableName($entityInstance);
      $query = "DROP TABLE";

      $query .= match ($options->dialect) {
        SQLDialect::MYSQL,
        SQLDialect::MARIADB => ' IF EXISTS',
        default => '',
      };
      $query .= " $dbName`$tableName`";

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
   * @param string $tableName
   * @param DataSource $dataSource
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
   * Checks whether a table has the given columns.
   *
   * @param string $tableName The name of the table to check.
   * @param array $columnNames An array of column names to check for.
   * @param DataSource $dataSource The data source to use.
   *
   * @return bool True if the table has all the given columns, false otherwise.
   * @throws ORMException when SQL statement execution fails.
   * @noinspection SqlResolve
   */
  public static function hasColumns(string $tableName, array $columnNames, DataSource $dataSource): bool
  {
    $columnNamesString = implode( ', ', array_map(fn($name) => "'$name'", $columnNames) );
    $totalColumNames = count($columnNames);

    if (empty($tableName) || empty($totalColumNames))
    {
      return false;
    }

    $databaseName = $dataSource->getDatabaseName();

    $sql = match ($dataSource->type) {
      DataSourceType::SQLITE => <<<EOF
SELECT COUNT(*) AS column_count
FROM pragma_table_info('$databaseName')
WHERE name IN ($columnNamesString)
HAVING COUNT(*) = $totalColumNames
EOF,

      DataSourceType::POSTGRESQL => <<<EOF
SELECT COUNT(*) AS column_count
FROM information_schema.columns
WHERE table_name = '$databaseName'
  AND column_name IN ($columnNamesString)
HAVING COUNT(*) = $totalColumNames;
EOF,

      default => <<<EOF
SELECT COUNT(*) as total_fields
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = '$databaseName'
    AND TABLE_NAME = '$tableName'
    AND COLUMN_NAME IN ($columnNamesString)
HAVING COUNT(*) = $totalColumNames
EOF
    };

    $statement = $dataSource->db->query($sql);

    if (false === $statement)
    {
      throw new ORMException(print_r($dataSource->db->errorInfo(), true));
    }

    $columns = self::getColumnNames($tableName, $dataSource, $databaseName);

    if (!$columnNames)
    {
      return false;
    }

    foreach($columnNames as $columnName)
    {
      if (!in_array($columnName, $columns))
      {
        return false;
      }
    }

    return true;
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

  /**
   * @param string $entityClass
   * @return object
   * @throws ClassNotFoundException
   * @throws ORMException
   * @throws ReflectionException
   */
  private static function getEntityInstance(string $entityClass): object
  {
    # Get the entity table
    $entityInspector = EntityInspector::getInstance();
    $entityInspector->validateEntityName($entityClass);

    $entityReflection = new ReflectionClass($entityClass);
    return $entityReflection->newInstance();
  }

  /**
   * @param string $tableName
   * @param DataSource $dataSource
   * @param string|null $databaseName
   * @return array
   * @throws ORMException
   */
  private static function getColumnNames(string $tableName, DataSource $dataSource, ?string $databaseName = null): array
  {
    $columnNames = [];
    $dbName = $databaseName ?? $dataSource->getDatabaseName();

    $query = "DESCRIBE `$dbName`.`$tableName`";

    $statement = $dataSource->db->query($query);

    if (false === $statement)
    {
      throw new ORMException("Failed to describe `$dbName`.`$tableName`: " . print_r($dataSource->db->errorInfo(), true));
    }

    /** @var SQLTableDescription[] $tableFields */
    $tableFields = $statement->fetchAll(PDO::FETCH_CLASS,SQLTableDescription::class);

    foreach ($tableFields as $tableField)
    {
      $columnNames[] = $tableField->Field;
    }

    return $columnNames;
  }

  /**
   * @param object $entityInstance
   * @param SchemaOptions $options
   * @return SQLTableDescription
   * @throws ClassNotFoundException
   * @throws DataSourceConnectionException
   * @throws ORMException
   */
  private static function getTableDescriptions(object $entityInstance, PDO|IDataObject $connection): array
  {
    $entityInspector = EntityInspector::getInstance();

    $tableName = $entityInspector->getTableName($entityInstance);

    # Describe the current table schema
    $statement = $connection->query("DESCRIBE `$tableName`");

    if (false === $statement->execute())
    {
      throw new ORMException("Failed to execute 'DESCRIBE `$tableName`'" . PHP_EOL . print_r($statement->errorInfo(), true));
    }

    return $statement->fetchAll(PDO::FETCH_CLASS, SQLTableDescription::class);
  }
}