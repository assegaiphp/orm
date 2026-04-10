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
    $options ??= new SchemaOptions();

    try
    {
      $reflection = new ReflectionClass(objectOrClass: $entityClass);
      $entityInstance = $reflection->newInstance();
      $resolvedOptions = self::resolveSchemaOptions($entityInstance, $options);
      $resolvedOptions = new SchemaOptions(
        dbName: $resolvedOptions->dbName,
        dialect: $resolvedOptions->dialect,
        entityPrefix: $resolvedOptions->entityPrefix,
        logging: $resolvedOptions->logging,
        dropSchema: $resolvedOptions->dropSchema,
        synchronize: $resolvedOptions->synchronize,
        checkIfExists: true,
        isTemporary: $resolvedOptions->isTemporary,
        characterSet: $resolvedOptions->characterSet,
        engine: $resolvedOptions->engine,
      );
      $db = DBFactory::getSQLConnection(dbName: $resolvedOptions->dbName, dialect: $resolvedOptions->dialect);

      $query = self::getDDLStatementFromEntity($entityInstance, $resolvedOptions);
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
    $db = DBFactory::getSQLConnection(dbName: $options->dbName, dialect: $options->dialect);
    $tableFields = self::getTableDescriptions($entityInstance, $db);

    if (empty($tableFields))
    {
      return null;
    }

    if ($options->dialect === SQLDialect::SQLITE)
    {
      return self::commitRebuiltSchemaChanges($db, $entityReflection, $entityInstance, $options, $tableFields);
    }

    $changes = self::compileChanges($entityReflection, $tableFields, $options->dialect);

    if (!self::hasSchemaChanges($changes))
    {
      return null;
    }

    if ($options->dialect === SQLDialect::POSTGRESQL)
    {
      return self::commitPostgreSqlSchemaChanges(
        $db,
        $entityInspector->getTableName($entityInstance),
        $changes,
        $tableFields,
      );
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
      SQLDialect::MARIADB => sprintf(
        '%s ENGINE=%s DEFAULT CHARSET=%s COLLATE=%s',
        $query,
        ($options->engine?->value ?? 'InnoDB'),
        ($options->characterSet ?? SQLCharacterSet::UTF8MB4)->value,
        ($options->characterSet ?? SQLCharacterSet::UTF8MB4)->getDefaultCollation(),
      ),
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

  private static function hasSchemaChanges(SchemaChangeManifest $changes): bool
  {
    return $changes->getAddList() !== []
      || $changes->getChangeList() !== []
      || $changes->getDropList() !== [];
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

  private static function commitRebuiltSchemaChanges(PDO|IDataObject $connection, ReflectionClass $entityReflection, object $entityInstance, SchemaOptions $options, array $tableFields): bool
  {
    $entityInspector = EntityInspector::getInstance();
    $tableName = $entityInspector->getTableName($entityInstance);
    $quotedTableName = SqlDialectHelper::quoteIdentifier($tableName, $options->dialect);
    $temporaryTableName = self::generateTemporaryTableName($tableName);
    $quotedTemporaryTableName = SqlDialectHelper::quoteIdentifier($temporaryTableName, $options->dialect);
    $createTableOptions = new SchemaOptions(
      dbName: $options->dbName,
      dialect: $options->dialect,
      entityPrefix: $options->entityPrefix,
      logging: $options->logging,
      dropSchema: false,
      synchronize: $options->synchronize,
      checkIfExists: false,
      isTemporary: false,
      characterSet: $options->characterSet,
      engine: $options->engine,
    );
    $createTableSql = self::getDDLStatementFromEntity($entityInstance, $createTableOptions);
    $temporaryCreateTableSql = self::replaceCreateTableName($createTableSql, $quotedTemporaryTableName);
    $targetColumns = self::getEntityColumnNames($entityReflection);
    $currentColumns = array_map(fn(SQLTableDescription $field) => $field->Field, $tableFields);
    $sharedColumns = array_values(array_intersect($currentColumns, $targetColumns));
    $requiresForeignKeyToggle = $options->dialect === SQLDialect::SQLITE;

    try
    {
      if ($requiresForeignKeyToggle)
      {
        self::executeDialectStatement($connection, 'PRAGMA foreign_keys = OFF', $options->dialect);
      }

      $connection->beginTransaction();
      self::executeDialectStatement($connection, $temporaryCreateTableSql, $options->dialect);

      if (!empty($sharedColumns))
      {
        $quotedColumns = implode(', ', array_map(
          fn(string $columnName) => SqlDialectHelper::quoteIdentifier($columnName, $options->dialect),
          $sharedColumns,
        ));

        self::executeDialectStatement(
          $connection,
          "INSERT INTO $quotedTemporaryTableName ($quotedColumns) SELECT $quotedColumns FROM $quotedTableName",
          $options->dialect
        );
      }

      self::executeDialectStatement($connection, "DROP TABLE $quotedTableName", $options->dialect);
      self::executeDialectStatement(
        $connection,
        'ALTER TABLE ' . $quotedTemporaryTableName . ' RENAME TO ' . SqlDialectHelper::quoteIdentifier($tableName, $options->dialect),
        $options->dialect
      );
      self::synchronizeRebuiltTableState($connection, $tableName, $options->dialect);
      $connection->commit();

      if ($requiresForeignKeyToggle)
      {
        self::executeDialectStatement($connection, 'PRAGMA foreign_keys = ON', $options->dialect);
      }
    }
    catch (PDOException $e)
    {
      if ($connection->inTransaction())
      {
        $connection->rollBack();
      }

      try
      {
        if ($requiresForeignKeyToggle)
        {
          self::executeDialectStatement($connection, 'PRAGMA foreign_keys = ON', $options->dialect);
        }
      }
      catch (ORMException)
      {
        // Best effort cleanup.
      }

      throw new ORMException($e->getMessage());
    }
    catch (ORMException $e)
    {
      if ($connection->inTransaction())
      {
        $connection->rollBack();
      }

      try
      {
        if ($requiresForeignKeyToggle)
        {
          self::executeDialectStatement($connection, 'PRAGMA foreign_keys = ON', $options->dialect);
        }
      }
      catch (ORMException)
      {
        // Best effort cleanup.
      }

      throw $e;
    }

    return true;
  }

  /**
   * @param PDO|IDataObject $connection
   * @param string $tableName
   * @param SchemaChangeManifest $changes
   * @param SQLTableDescription[] $tableFields
   * @return bool
   * @throws ORMException
   */
  private static function commitPostgreSqlSchemaChanges(
    PDO|IDataObject $connection,
    string $tableName,
    SchemaChangeManifest $changes,
    array $tableFields,
  ): bool {
    $quotedTableName = SqlDialectHelper::quoteIdentifier($tableName, SQLDialect::POSTGRESQL);
    $tableFieldMap = [];

    foreach ($tableFields as $tableField)
    {
      if (is_string($tableField->Field) && $tableField->Field !== '')
      {
        $tableFieldMap[$tableField->Field] = $tableField;
      }
    }

    try
    {
      $connection->beginTransaction();

      foreach ($changes->getDropList() as $dropStatement)
      {
        $columnName = $dropStatement->columnDefinition?->name ?: $dropStatement->columnName;
        $quotedColumnName = SqlDialectHelper::quoteIdentifier($columnName, SQLDialect::POSTGRESQL);
        self::executeDialectStatement(
          $connection,
          "ALTER TABLE $quotedTableName DROP COLUMN IF EXISTS $quotedColumnName",
          SQLDialect::POSTGRESQL,
        );
      }

      foreach ($changes->getAddList() as $addStatement)
      {
        self::executeDialectStatement(
          $connection,
          "ALTER TABLE $quotedTableName ADD COLUMN {$addStatement->columnDefinition}",
          SQLDialect::POSTGRESQL,
        );
      }

      foreach ($changes->getChangeList() as $changeStatement)
      {
        $columnName = $changeStatement->columnDefinition->name ?: $changeStatement->columnName;
        $currentField = $tableFieldMap[$columnName] ?? null;

        self::applyPostgreSqlColumnChange(
          $connection,
          $tableName,
          $columnName,
          $changeStatement->columnDefinition,
          $currentField,
        );
      }

      self::synchronizePostgreSqlSequences($connection, $tableName);
      $connection->commit();
    }
    catch (PDOException $e)
    {
      if ($connection->inTransaction())
      {
        $connection->rollBack();
      }

      throw new ORMException($e->getMessage());
    }
    catch (ORMException $e)
    {
      if ($connection->inTransaction())
      {
        $connection->rollBack();
      }

      throw $e;
    }

    return true;
  }

  private static function applyPostgreSqlColumnChange(
    PDO|IDataObject $connection,
    string $tableName,
    string $columnName,
    \Assegai\Orm\Queries\Sql\SQLColumnDefinition $columnDefinition,
    ?SQLTableDescription $currentField,
  ): void {
    $quotedTableName = SqlDialectHelper::quoteIdentifier($tableName, SQLDialect::POSTGRESQL);
    $quotedColumnName = SqlDialectHelper::quoteIdentifier($columnName, SQLDialect::POSTGRESQL);

    self::executeDialectStatement(
      $connection,
      sprintf(
        'ALTER TABLE %s ALTER COLUMN %s TYPE %s',
        $quotedTableName,
        $quotedColumnName,
        $columnDefinition->getTypeExpression(),
      ),
      SQLDialect::POSTGRESQL,
    );

    $defaultExpression = $columnDefinition->getDefaultExpression();

    if ($defaultExpression === null)
    {
      self::executeDialectStatement(
        $connection,
        "ALTER TABLE $quotedTableName ALTER COLUMN $quotedColumnName DROP DEFAULT",
        SQLDialect::POSTGRESQL,
      );
    }
    else
    {
      self::executeDialectStatement(
        $connection,
        "ALTER TABLE $quotedTableName ALTER COLUMN $quotedColumnName SET DEFAULT $defaultExpression",
        SQLDialect::POSTGRESQL,
      );
    }

    if ($columnDefinition->isNullable())
    {
      self::executeDialectStatement(
        $connection,
        "ALTER TABLE $quotedTableName ALTER COLUMN $quotedColumnName DROP NOT NULL",
        SQLDialect::POSTGRESQL,
      );
    }
    else
    {
      if ($defaultExpression !== null && ($currentField?->Null ?? 'YES') === 'YES')
      {
        self::executeDialectStatement(
          $connection,
          "UPDATE $quotedTableName SET $quotedColumnName = $defaultExpression WHERE $quotedColumnName IS NULL",
          SQLDialect::POSTGRESQL,
        );
      }

      self::executeDialectStatement(
        $connection,
        "ALTER TABLE $quotedTableName ALTER COLUMN $quotedColumnName SET NOT NULL",
        SQLDialect::POSTGRESQL,
      );
    }

    self::synchronizePostgreSqlColumnConstraints($connection, $tableName, $columnName, $currentField, $columnDefinition);
  }

  private static function synchronizePostgreSqlColumnConstraints(
    PDO|IDataObject $connection,
    string $tableName,
    string $columnName,
    ?SQLTableDescription $currentField,
    \Assegai\Orm\Queries\Sql\SQLColumnDefinition $columnDefinition,
  ): void {
    $currentKey = $currentField?->Key ?? '';
    $wantsPrimaryKey = $columnDefinition->isPrimaryKey();
    $wantsUnique = !$wantsPrimaryKey && $columnDefinition->isUnique();
    $hasPrimaryKey = $currentKey === 'PRI';
    $hasUniqueKey = $currentKey === 'UNI';
    $quotedTableName = SqlDialectHelper::quoteIdentifier($tableName, SQLDialect::POSTGRESQL);
    $quotedColumnName = SqlDialectHelper::quoteIdentifier($columnName, SQLDialect::POSTGRESQL);

    if (!$wantsPrimaryKey && $hasPrimaryKey)
    {
      foreach (self::getPostgreSqlConstraintNames($connection, $tableName, $columnName, 'p') as $constraintName)
      {
        $quotedConstraintName = SqlDialectHelper::quoteIdentifier($constraintName, SQLDialect::POSTGRESQL);
        self::executeDialectStatement(
          $connection,
          "ALTER TABLE $quotedTableName DROP CONSTRAINT IF EXISTS $quotedConstraintName",
          SQLDialect::POSTGRESQL,
        );
      }
    }

    if (!$wantsUnique && $hasUniqueKey)
    {
      foreach (self::getPostgreSqlConstraintNames($connection, $tableName, $columnName, 'u') as $constraintName)
      {
        $quotedConstraintName = SqlDialectHelper::quoteIdentifier($constraintName, SQLDialect::POSTGRESQL);
        self::executeDialectStatement(
          $connection,
          "ALTER TABLE $quotedTableName DROP CONSTRAINT IF EXISTS $quotedConstraintName",
          SQLDialect::POSTGRESQL,
        );
      }
    }

    if ($wantsPrimaryKey && !$hasPrimaryKey)
    {
      self::executeDialectStatement(
        $connection,
        "ALTER TABLE $quotedTableName ADD PRIMARY KEY ($quotedColumnName)",
        SQLDialect::POSTGRESQL,
      );
    }

    if ($wantsUnique && !$hasUniqueKey)
    {
      $constraintName = self::generatePostgreSqlUniqueConstraintName($tableName, $columnName);
      $quotedConstraintName = SqlDialectHelper::quoteIdentifier($constraintName, SQLDialect::POSTGRESQL);

      self::executeDialectStatement(
        $connection,
        "ALTER TABLE $quotedTableName ADD CONSTRAINT $quotedConstraintName UNIQUE ($quotedColumnName)",
        SQLDialect::POSTGRESQL,
      );
    }
  }

  /**
   * @return string[]
   * @throws ORMException
   */
  private static function getPostgreSqlConstraintNames(
    PDO|IDataObject $connection,
    string $tableName,
    string $columnName,
    string $constraintType,
  ): array {
    $statement = $connection->prepare(
      <<<'SQL'
SELECT con.conname
FROM pg_constraint con
JOIN pg_class rel ON rel.oid = con.conrelid
JOIN pg_namespace n ON n.oid = rel.relnamespace
JOIN pg_attribute a ON a.attrelid = rel.oid AND a.attnum = ANY(con.conkey)
WHERE rel.relname = :table
  AND n.nspname = current_schema()
  AND con.contype = :type
  AND a.attname = :column
  AND array_length(con.conkey, 1) = 1
ORDER BY con.conname
SQL
    );

    if (!$statement || !$statement->execute([
      'table' => $tableName,
      'type' => $constraintType,
      'column' => $columnName,
    ])) {
      throw new ORMException("Failed to inspect PostgreSQL constraints for '$tableName.$columnName'.");
    }

    /** @var array<int, array{conname?: mixed}> $rows */
    $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

    return array_values(array_filter(
      array_map(
        static fn(array $row): ?string => is_string($row['conname'] ?? null) ? $row['conname'] : null,
        $rows,
      ),
      static fn(?string $constraintName): bool => $constraintName !== null && $constraintName !== '',
    ));
  }

  private static function generatePostgreSqlUniqueConstraintName(string $tableName, string $columnName): string
  {
    return preg_replace('/[^A-Za-z0-9_]+/', '_', "{$tableName}_{$columnName}_key") ?: "{$tableName}_{$columnName}_key";
  }

  private static function getEntityColumnNames(ReflectionClass $entityReflection): array
  {
    $columnInspector = ColumnInspector::getInstance();
    $columnNames = [];

    foreach ($entityReflection->getProperties(ReflectionProperty::IS_PUBLIC) as $propertyReflection)
    {
      if (!$columnInspector->propertyHasColumnAttribute($propertyReflection))
      {
        continue;
      }

      $column = $columnInspector->getMetaDataFromReflection($propertyReflection);
      $columnNames[] = $column->name ?: $propertyReflection->getName();
    }

    return $columnNames;
  }

  private static function replaceCreateTableName(string $query, string $quotedTemporaryTableName): string
  {
    $updatedQuery = preg_replace(
      '/^(CREATE(?:\s+TEMPORARY)?\s+TABLE(?:\s+IF\s+NOT\s+EXISTS)?\s+)([`\"]?[A-Za-z0-9_]+[`\"]?)/i',
      '$1' . $quotedTemporaryTableName,
      $query,
      1,
    );

    if (!is_string($updatedQuery))
    {
      throw new ORMException('Failed to rewrite the CREATE TABLE statement for schema alteration.');
    }

    return $updatedQuery;
  }

  private static function generateTemporaryTableName(string $tableName): string
  {
    return '__assegai_tmp_' . $tableName . '_' . str_replace('.', '', uniqid('', true));
  }

  private static function executeDialectStatement(PDO|IDataObject $connection, string $query, SQLDialect $dialect): void
  {
    if (false === $connection->exec($query))
    {
      $errorMessage = json_encode($connection->errorInfo());
      $label = strtolower($dialect->value);
      throw new ORMException($errorMessage ?: "Failed to execute {$label} statement: $query");
    }
  }

  private static function synchronizeRebuiltTableState(PDO|IDataObject $connection, string $tableName, SQLDialect $dialect): void
  {
    if ($dialect !== SQLDialect::POSTGRESQL) {
      return;
    }

    self::synchronizePostgreSqlSequences($connection, $tableName);
  }

  private static function synchronizePostgreSqlSequences(PDO|IDataObject $connection, string $tableName): void
  {
    $tableLiteral = $connection->quote($tableName);
    $sql = <<<SQL
SELECT
  a.attname AS column_name,
  pg_get_serial_sequence(format('%I.%I', current_schema(), c.relname), a.attname) AS sequence_name
FROM pg_attribute a
JOIN pg_class c ON c.oid = a.attrelid
JOIN pg_namespace n ON n.oid = c.relnamespace
LEFT JOIN pg_attrdef ad ON ad.adrelid = a.attrelid AND ad.adnum = a.attnum
WHERE c.relkind = 'r'
  AND n.nspname = current_schema()
  AND c.relname = $tableLiteral
  AND a.attnum > 0
  AND NOT a.attisdropped
  AND (
    a.attidentity IN ('a', 'd')
    OR pg_get_expr(ad.adbin, ad.adrelid) LIKE 'nextval(%'
  )
ORDER BY a.attnum
SQL;

    $statement = $connection->query($sql);

    if (!$statement || !$statement->execute()) {
      throw new ORMException("Failed to inspect PostgreSQL sequences for '$tableName'.");
    }

    /** @var array<int, array{column_name?: mixed, sequence_name?: mixed}> $rows */
    $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {
      $columnName = is_string($row['column_name'] ?? null) ? $row['column_name'] : null;
      $sequenceName = is_string($row['sequence_name'] ?? null) ? $row['sequence_name'] : null;

      if ($columnName === null || $sequenceName === null || $sequenceName === '') {
        continue;
      }

      $quotedSequenceName = $connection->quote($sequenceName);
      $quotedTableName = SqlDialectHelper::quoteIdentifier($tableName, SQLDialect::POSTGRESQL);
      $quotedColumnName = SqlDialectHelper::quoteIdentifier($columnName, SQLDialect::POSTGRESQL);
      $syncSql = <<<SQL
SELECT setval(
  $quotedSequenceName,
  COALESCE((SELECT MAX($quotedColumnName) FROM $quotedTableName), 1),
  (SELECT MAX($quotedColumnName) IS NOT NULL FROM $quotedTableName)
)
SQL;

      self::executeDialectStatement($connection, $syncSql, SQLDialect::POSTGRESQL);
    }
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
      SQLDialect::POSTGRESQL => self::getPostgreSqlTableDefinitionSql($connection, $tableName),
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

  private static function getPostgreSqlTableDefinitionSql(PDO $connection, string $tableName): ?string
  {
    $tableFields = self::getPostgreSqlTableDescriptions($connection, $tableName);

    if (empty($tableFields)) {
      return null;
    }

    $quotedTableName = SqlDialectHelper::quoteIdentifier($tableName, SQLDialect::POSTGRESQL);
    $definitions = array_map(
      fn(SQLTableDescription $field): string => '  ' . self::buildPostgreSqlColumnDefinition($field),
      $tableFields,
    );

    return sprintf("CREATE TABLE %s (\n%s\n)", $quotedTableName, implode(",\n", $definitions));
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
  a.attname AS "Field",
  pg_catalog.format_type(a.atttypid, a.atttypmod) AS "Type",
  CASE WHEN a.attnotnull THEN 'NO' ELSE 'YES' END AS "Null",
  CASE
    WHEN EXISTS (
      SELECT 1
      FROM pg_constraint con
      WHERE con.conrelid = c.oid
        AND con.contype = 'p'
        AND a.attnum = ANY(con.conkey)
    ) THEN 'PRI'
    WHEN EXISTS (
      SELECT 1
      FROM pg_constraint con
      WHERE con.conrelid = c.oid
        AND con.contype = 'u'
        AND a.attnum = ANY(con.conkey)
    ) THEN 'UNI'
    ELSE ''
  END AS "Key",
  pg_get_expr(ad.adbin, ad.adrelid) AS "Default",
  CASE
    WHEN a.attidentity IN ('a', 'd') OR pg_get_expr(ad.adbin, ad.adrelid) LIKE 'nextval(%' THEN 'auto_increment'
    ELSE ''
  END AS "Extra"
FROM pg_attribute a
JOIN pg_class c ON c.oid = a.attrelid
JOIN pg_namespace n ON n.oid = c.relnamespace
LEFT JOIN pg_attrdef ad ON ad.adrelid = a.attrelid AND ad.adnum = a.attnum
WHERE c.relkind = 'r'
  AND n.nspname = current_schema()
  AND c.relname = $quotedTableName
  AND a.attnum > 0
  AND NOT a.attisdropped
ORDER BY a.attnum
SQL;

    $statement = $connection->query($sql);

    if (!$statement || !$statement->execute()) {
      throw new ORMException("Failed to load PostgreSQL table metadata for '$tableName'.");
    }

    return $statement->fetchAll(PDO::FETCH_CLASS, SQLTableDescription::class);
  }

  private static function buildPostgreSqlColumnDefinition(SQLTableDescription $field): string
  {
    $columnName = SqlDialectHelper::quoteIdentifier((string) $field->Field, SQLDialect::POSTGRESQL);
    $definition = $columnName . ' ' . (string) $field->Type;

    if (($field->Null ?? 'YES') === 'NO') {
      $definition .= ' NOT NULL';
    }

    if (is_string($field->Default) && $field->Default !== '') {
      $definition .= ' DEFAULT ' . $field->Default;
    } elseif (($field->Extra ?? '') === 'auto_increment') {
      $definition .= ' GENERATED BY DEFAULT AS IDENTITY';
    }

    if (($field->Key ?? '') === 'PRI') {
      $definition .= ' PRIMARY KEY';
    } elseif (($field->Key ?? '') === 'UNI') {
      $definition .= ' UNIQUE';
    }

    return $definition;
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
