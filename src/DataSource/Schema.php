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
use Assegai\Orm\Util\SqlDialectHelper;
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
      $options = self::resolveSchemaOptions($entityInstance, $options);
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
      $options = self::resolveSchemaOptions($entityInstance, $options);
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
    $options ??= new SchemaOptions();
    $fromTable = self::getQualifiedTableName($from, $options);
    $toTable = match ($options->dialect) {
      SQLDialect::SQLITE,
      SQLDialect::POSTGRESQL => SqlDialectHelper::quoteIdentifier($to, $options->dialect),
      default => self::getQualifiedTableName($to, $options),
    };

    $query = match ($options->dialect) {
      SQLDialect::SQLITE,
      SQLDialect::POSTGRESQL => "ALTER TABLE $fromTable RENAME TO $toTable",
      default => "RENAME TABLE $fromTable TO $toTable",
    };

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
    $options = self::resolveSchemaOptions($entityInstance, $options);

    if ($options->dialect === SQLDialect::SQLITE) {
      throw new ORMException('Schema alterations are not supported for SQLite yet.');
    }

    $db = DBFactory::getSQLConnection(dbName: $options->dbName, dialect: $options->dialect);
    $tableFields = self::getTableDescriptions($entityInstance, $db);

    if (empty($tableFields))
    {
      return null;
    }

    $changes = self::compileChanges($entityReflection, $tableFields, $options->dialect);

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
    $options = self::resolveSchemaOptions($entityInstance, $options);
    $entityInspector = EntityInspector::getInstance();

    $tableName = $entityInspector->getTableName($entityInstance);

    # Describe the current table schema
    $db = DBFactory::getSQLConnection(dbName: $options->dbName, dialect: $options->dialect);
    $tableFields = self::getTableDescriptions($entityInstance, $db);

    if (empty($tableFields))
    {
      return null;
    }

    $result = self::getTableDefinitionSql($db, $tableName, $options->dialect);

    if (!is_string($result))
    {
      throw new ORMException("Invalid DDL statement for table `$tableName`");
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
    $options = self::resolveSchemaOptions($entityInstance, $options);
    $tableName = $entityInspector->getTableName($entityInstance);
    $qualifiedTableName = self::getQualifiedTableName($tableName, $options);
    $query = $options->dialect === SQLDialect::SQLITE
      ? "DELETE FROM $qualifiedTableName"
      : "TRUNCATE TABLE $qualifiedTableName";

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

    if ($result && $options->dialect === SQLDialect::SQLITE) {
      $sequenceTable = SqlDialectHelper::quoteIdentifier('sqlite_sequence', SQLDialect::SQLITE);
      $quotedTableName = $db->quote($tableName);
      try {
        $db->exec("DELETE FROM $sequenceTable WHERE name = $quotedTableName");
      } catch (PDOException) {
        // sqlite_sequence only exists for AUTOINCREMENT tables.
      }
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
      $options = self::resolveSchemaOptions($entityInstance, $options);
      $tableName = $entityInspector->getTableName($entityInstance);
      $query = 'DROP TABLE ' . self::getQualifiedTableName($tableName, $options);

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
      $options = self::resolveSchemaOptions($entityInstance, $options);
      $tableName = $entityInspector->getTableName($entityInstance);
      $query = 'DROP TABLE IF EXISTS ' . self::getQualifiedTableName($tableName, $options);

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
    if (empty($tableName)) {
      return false;
    }

    $dialect = $dataSource->getDialect();
    $query = match ($dialect) {
      SQLDialect::SQLITE => "SELECT name FROM sqlite_master WHERE type = 'table' AND name = " . $dataSource->getClient()->quote($tableName),
      SQLDialect::POSTGRESQL => "SELECT table_name FROM information_schema.tables WHERE table_name = " . $dataSource->getClient()->quote($tableName),
      default => "SHOW TABLES LIKE " . $dataSource->getClient()->quote($tableName),
    };

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
    $columnNames = array_values(array_filter($columnNames, fn($name) => is_string($name) && $name !== ''));

    if (empty($tableName) || empty($columnNames))
    {
      return false;
    }

    $columns = self::getColumnNames($tableName, $dataSource);

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
    $options = self::resolveSchemaOptions($entityInstance, $options);
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
        $createDefinitions .= SqlDialectHelper::quoteIdentifier($reflectionProperty->getName(), $options->dialect) . ' ';
      }
      $createDefinitions .= $columnAttributeInstance->getSqlDefinition($options->dialect) . ',' . PHP_EOL;
    }

    $createDefinitions = trim($createDefinitions, ",\t\n\r\0\x0B");
    $qualifiedTableName = self::getQualifiedTableName($tableName, $options);

    $query = "CREATE{$temporary}TABLE{$ifExists}{$qualifiedTableName} ($createDefinitions)";

    return match ($options->dialect) {
      SQLDialect::MYSQL,
      SQLDialect::MARIADB => $query . ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci',
      default => $query,
    };
  }

  /**
   * @param ReflectionClass $entityReflection
   * @param SQLTableDescription[] $currentTableFields
   * @return SchemaChangeManifest
   * @throws ClassNotFoundException
   * @throws ORMException
   * @throws ReflectionException
   */
  private static function compileChanges(ReflectionClass $entityReflection, array $currentTableFields, SQLDialect $dialect = SQLDialect::MYSQL): SchemaChangeManifest
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
      $changes->add(new DDLAddStatement($columnName, $column->getSqlDefinition($dialect)));
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
        $changes->change(new DDLChangeStatement($tableField->Field, $columnAttribute->getSqlDefinition($dialect)));
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

    if ($tableField->Type !== $columnAttribute->getFieldType())
    {
      return true;
    }

    if ($tableField->Extra !== $columnAttribute->getFieldExtra())
    {
      return true;
    }

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
    $tableFields = self::getTableDescriptions(
      (object)['__tableName' => $tableName],
      $dataSource->getClient()
    );

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
    $tableName = property_exists($entityInstance, '__tableName')
      ? $entityInstance->__tableName
      : $entityInspector->getTableName($entityInstance);
    $dialect = SqlDialectHelper::fromPdo($connection);

    return match ($dialect) {
      SQLDialect::SQLITE => self::getSQLiteTableDescriptions($connection, $tableName),
      SQLDialect::POSTGRESQL => self::getPostgreSqlTableDescriptions($connection, $tableName),
      default => self::getMySqlTableDescriptions($connection, $tableName),
    };
  }

  private static function getQualifiedTableName(string $tableName, SchemaOptions $options): string
  {
    return SqlDialectHelper::qualifyTable($tableName, $options->dbName, $options->dialect);
  }

  private static function resolveSchemaOptions(object $entityInstance, ?SchemaOptions $options): SchemaOptions
  {
    $options ??= new SchemaOptions();

    $entityMetadata = EntityInspector::getInstance()->getMetaData($entityInstance);
    $dbName = $options->dbName ?: ($entityMetadata->database ?? '');
    $dialect = $options->dialect;

    if (
      $options->dialect === SQLDialect::MYSQL &&
      $entityMetadata->driver &&
      $entityMetadata->driver !== DataSourceType::MYSQL
    ) {
      $dialect = SqlDialectHelper::fromDataSourceType($entityMetadata->driver);
    }

    return new SchemaOptions(
      dbName: $dbName,
      dialect: $dialect,
      entityPrefix: $options->entityPrefix,
      logging: $options->logging,
      dropSchema: $options->dropSchema,
      synchronize: $options->synchronize,
      checkIfExists: $options->checkIfExists,
      isTemporary: $options->isTemporary,
      characterSet: $options->characterSet,
      engine: $options->engine,
    );
  }

  private static function getTableDefinitionSql(PDO $connection, string $tableName, SQLDialect $dialect): ?string
  {
    return match ($dialect) {
      SQLDialect::SQLITE => self::getSQLiteTableDefinitionSql($connection, $tableName),
      default => self::getMySqlTableDefinitionSql($connection, $tableName),
    };
  }

  private static function getMySqlTableDefinitionSql(PDO $connection, string $tableName): ?string
  {
    $quotedTableName = SqlDialectHelper::quoteIdentifier($tableName, SQLDialect::MYSQL);
    $statement = $connection->query("SHOW CREATE TABLE $quotedTableName");

    if (!$statement || !$statement->execute()) {
      throw new ORMException("Failed to execute 'SHOW CREATE TABLE $quotedTableName'" . PHP_EOL . print_r($statement?->errorInfo(), true));
    }

    $result = $statement->fetchColumn(1);
    return is_string($result) ? $result : null;
  }

  private static function getSQLiteTableDefinitionSql(PDO $connection, string $tableName): ?string
  {
    $quotedTableName = $connection->quote($tableName);
    $statement = $connection->query("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = $quotedTableName");

    if (!$statement || !$statement->execute()) {
      throw new ORMException("Failed to load SQLite table definition for '$tableName'.");
    }

    $result = $statement->fetchColumn();
    return is_string($result) ? $result : null;
  }

  private static function getMySqlTableDescriptions(PDO $connection, string $tableName): array
  {
    $quotedTableName = SqlDialectHelper::quoteIdentifier($tableName, SQLDialect::MYSQL);
    $statement = $connection->query("DESCRIBE $quotedTableName");

    if (!$statement || !$statement->execute()) {
      throw new ORMException("Failed to execute 'DESCRIBE $quotedTableName'" . PHP_EOL . print_r($statement?->errorInfo(), true));
    }

    return $statement->fetchAll(PDO::FETCH_CLASS, SQLTableDescription::class);
  }

  private static function getPostgreSqlTableDescriptions(PDO $connection, string $tableName): array
  {
    $quotedTableName = $connection->quote($tableName);
    $sql = <<<SQL
SELECT
  column_name AS "Field",
  data_type AS "Type",
  CASE WHEN is_nullable = 'YES' THEN 'YES' ELSE 'NO' END AS "Null",
  CASE WHEN column_default LIKE 'nextval(%' THEN 'PRI' ELSE '' END AS "Key",
  column_default AS "Default",
  CASE WHEN column_default LIKE 'nextval(%' THEN 'auto_increment' ELSE '' END AS "Extra"
FROM information_schema.columns
WHERE table_name = $quotedTableName
ORDER BY ordinal_position
SQL;

    $statement = $connection->query($sql);

    if (!$statement || !$statement->execute()) {
      throw new ORMException("Failed to load PostgreSQL table metadata for '$tableName'.");
    }

    return $statement->fetchAll(PDO::FETCH_CLASS, SQLTableDescription::class);
  }

  private static function getSQLiteTableDescriptions(PDO $connection, string $tableName): array
  {
    $quotedTableName = $connection->quote($tableName);
    $statement = $connection->query("PRAGMA table_info($quotedTableName)");

    if (!$statement || !$statement->execute()) {
      throw new ORMException("Failed to load SQLite table metadata for '$tableName'.");
    }

    $tableDefinition = self::getSQLiteTableDefinitionSql($connection, $tableName) ?? '';
    $autoIncrementColumns = [];
    if (preg_match_all('/[`"]?([A-Za-z0-9_]+)[`"]?\s+INTEGER\s+PRIMARY\s+KEY\s+AUTOINCREMENT/i', $tableDefinition, $matches)) {
      $autoIncrementColumns = $matches[1];
    }

    $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

    return array_map(
      fn(array $row) => self::mapSqliteTableRow($row, $autoIncrementColumns),
      $rows
    );
  }

  private static function mapSqliteTableRow(array $row, array $autoIncrementColumns = []): SQLTableDescription
  {
    $description = new SQLTableDescription();
    $description->Field = $row['name'] ?? '';
    $description->Type = strtolower((string)($row['type'] ?? ''));
    $description->Null = (isset($row['notnull']) && intval($row['notnull']) === 1) ? 'NO' : 'YES';
    $description->Key = (isset($row['pk']) && intval($row['pk']) === 1) ? 'PRI' : '';
    $defaultValue = $row['dflt_value'] ?? null;
    $description->Default = is_string($defaultValue)
      ? trim($defaultValue, "'\"")
      : ($defaultValue === null ? null : (string)$defaultValue);
    $description->Extra = in_array($description->Field, $autoIncrementColumns, true) ? 'auto_increment' : '';

    return $description;
  }
}
