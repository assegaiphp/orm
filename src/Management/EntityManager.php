<?php

namespace Assegai\Orm\Management;

use Assegai\Core\Config;
use Assegai\Core\ModuleManager;
use Assegai\Core\Util\Debug\Log;
use Assegai\Orm\Attributes\Columns\Column;
use Assegai\Orm\Attributes\Columns\CreateDateColumn;
use Assegai\Orm\Attributes\Columns\DeleteDateColumn;
use Assegai\Orm\Attributes\Columns\EmailColumn;
use Assegai\Orm\Attributes\Columns\PasswordColumn;
use Assegai\Orm\Attributes\Columns\PrimaryGeneratedColumn;
use Assegai\Orm\Attributes\Columns\UpdateDateColumn;
use Assegai\Orm\Attributes\Columns\URLColumn;
use Assegai\Orm\Attributes\Entity;
use Assegai\Orm\DataSource\DataSource;
use Assegai\Orm\Enumerations\RelationType;
use Assegai\Orm\Exceptions\ClassNotFoundException;
use Assegai\Orm\Exceptions\ContainerException;
use Assegai\Orm\Exceptions\EmptyCriteriaException;
use Assegai\Orm\Exceptions\GeneralSQLQueryException;
use Assegai\Orm\Exceptions\IllegalTypeException;
use Assegai\Orm\Exceptions\NotFoundException;
use Assegai\Orm\Exceptions\NotImplementedException;
use Assegai\Orm\Exceptions\ORMException;
use Assegai\Orm\Exceptions\SaveException;
use Assegai\Orm\Exceptions\TypeConversionException;
use Assegai\Orm\Exceptions\ValidationException;
use Assegai\Orm\Interfaces\IEntityStoreOwner;
use Assegai\Orm\Interfaces\IFactory;
use Assegai\Orm\Interfaces\QueryResultInterface;
use Assegai\Orm\Management\Inspectors\EntityInspector;
use Assegai\Orm\Management\Options\FindManyOptions;
use Assegai\Orm\Management\Options\FindOneOptions;
use Assegai\Orm\Management\Options\FindOptions;
use Assegai\Orm\Management\Options\FindWhereOptions;
use Assegai\Orm\Management\Options\InsertOptions;
use Assegai\Orm\Management\Options\RemoveOptions;
use Assegai\Orm\Management\Options\UpdateOptions;
use Assegai\Orm\Management\Options\UpsertOptions;
use Assegai\Orm\Metadata\RelationPropertyMetadata;
use Assegai\Orm\Queries\QueryBuilder\Results\DeleteResult;
use Assegai\Orm\Queries\QueryBuilder\Results\FindResult;
use Assegai\Orm\Queries\QueryBuilder\Results\InsertResult;
use Assegai\Orm\Queries\QueryBuilder\Results\UpdateResult;
use Assegai\Orm\Queries\Sql\SQLQuery;
use Assegai\Orm\Queries\Sql\SQLQueryResult;
use Assegai\Orm\Queries\Sql\SQLTableReference;
use Assegai\Orm\Util\Filter;
use Assegai\Orm\Util\Log\Logger;
use Assegai\Orm\Util\TypeConversion\GeneralConverters;
use Assegai\Orm\Util\TypeConversion\TypeResolver;
use DateTime;
use Exception;
use JetBrains\PhpStorm\ArrayShape;
use NumberFormatter;
use PDOStatement;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use stdClass;
use Symfony\Component\Console\Output\ConsoleOutput;
use UnitEnum;

/**
 * Class EntityManager. The EntityManager is the central access point to ORM functionality.
 * @package Assegai\Orm\Management
 *
 * @template T of object
 */
class EntityManager implements IEntityStoreOwner
{
  const LOG_TAG = '[Entity Manager]';
  /**
   * Once created and then reused by repositories.
   */
  protected array $entities = [];

  /**
   * @var array|string[]
   */
  protected array $readonlyColumns = ['id', 'createdAt', 'updatedAt', 'deletedAt'];
  /**
   * @var array|string[]
   */
  protected array $secure = ['password'];
  /**
   * @var array
   */
  protected array $customSecure = [];

  /**
   * @var array An array of default converters.
   */
  protected array $defaultConverters = [];

  /**
   * @var GeneralConverters[] An array of custom converters.
   */
  protected array $customConverters = [];
  /**
   * @var bool Is debug mode enabled.
   */
  protected bool $isDebug = false;
  /**
   * @var LoggerInterface The logger instance.
   */
  protected LoggerInterface $logger;

  /**
   * Constructs a new EntityManager instance.
   *
   * @param DataSource $connection The data source to use.
   * @param SQLQuery|null $query The query to use.
   * @param EntityInspector|null $inspector The entity inspector to use.
   * @param TypeResolver|null $typeResolver The type resolver to use.
   * @throws ReflectionException
   */
  public function __construct(protected DataSource $connection, protected ?SQLQuery $query = null, protected ?EntityInspector $inspector = null, protected ?TypeResolver $typeResolver = null)
  {
    $this->logger = new Logger(new ConsoleOutput());
    $this->query = $query ?? new SQLQuery(db: $connection->getClient());

    // TODO: *BREAKING_CHANGE* Remove this binding as it breaks the inversion of control principal
    if (!$this->inspector) {
      $this->inspector = EntityInspector::getInstance();
      $this->inspector->setLogger($this->logger);
    }

    // TODO: *BREAKING_CHANGE* Remove this binding as it breaks the inversion of control principal
    if (!$this->typeResolver) {
      $this->typeResolver = TypeResolver::getInstance();
    }

    $this->defaultConverters[] = new GeneralConverters();
    if ($customConverters = ModuleManager::getInstance()->getConfig('converters')) {
      foreach ($customConverters as $converterClassName) {
        $converterReflection = new ReflectionClass($converterClassName);
        $customConvertor = $converterReflection->newInstance();
        $this->customConverters[] = $customConvertor;
      }
    }

    $isDebugMode = strtolower($_ENV['DEBUG_MODE']);
    $this->isDebug = !in_array($_ENV['ENV'], ['PROD', 'PRODUCTION']) && boolval($isDebugMode) === true;
  }

  /**
   * @param string|object $objectOrClass
   * @return bool
   * @throws ReflectionException
   */
  public static function objectOrClassIsNotEntity(string|object $objectOrClass): bool
  {
    return !self::objectOrClassIsEntity(objectOrClass: $objectOrClass);
  }

  /**
   * Checks if the given object or class name is a valid Entity class name.
   *
   * @param string|object $objectOrClass The name of the class to check.
   * @return bool Returns `true` if the given class name is an Entity instance.
   * @throws ReflectionException
   */
  public static function objectOrClassIsEntity(string|object $objectOrClass): bool
  {
    $reflectionClass = new ReflectionClass($objectOrClass);
    $entityAttributes = $reflectionClass->getAttributes(Entity::class);

    if ($entityAttributes) {
      return true;
    }

    if ($objectOrClass instanceof Entity) {
      return true;
    }

    if (in_array(Entity::class, class_implements($objectOrClass))) {
      return true;
    }

    return false;
  }

  /**
   * Executes a raw SQL query and returns the raw database results.
   *
   * @param string $query
   * @param array $parameters
   *
   * @return PDOStatement|false Returns a PDOStatement object, or FALSE on failure.
   * @link https://php.net/manual/en/pdo.query.php
   */
  public function query(string $query, array $parameters = []): PDOStatement|false
  {
    // TODO: Add support for all raw query parameters e.g. mode, ...fetch_mode_args
    return $this->connection->getClient()->query($query);
  }

  /**
   * Saves all given entities in the database.
   * If entities do not exist in the database then inserts, otherwise updates.
   *
   * @param object|object[] $targetOrEntity The entity or entities to save.
   * @param InsertOptions|null $options The insert options.
   * @return QueryResultInterface
   * @throws ClassNotFoundException
   * @throws EmptyCriteriaException
   * @throws GeneralSQLQueryException
   * @throws IllegalTypeException
   * @throws ORMException
   * @throws ReflectionException
   * @throws SaveException
   */
  public function save(object|array $targetOrEntity, InsertOptions|UpdateOptions|null $options = null): QueryResultInterface
  {
    $results = [];

    /** @var object $targetOrEntity */
    if (is_object($targetOrEntity)) {
      if (empty($targetOrEntity->id)) {
        if (!$options instanceof InsertOptions) {
          $this->logger->warning("InsertOptions not provided. Using default InsertOptions.");
          $options = new InsertOptions();
        }
        $saveResult = $this->insert(entityClass: $targetOrEntity::class, entity: $targetOrEntity, options: $options);
      } else if ($this->findBy($targetOrEntity::class, new FindWhereOptions(conditions: ['id' => $targetOrEntity->id]))) {
        if (!$options instanceof UpdateOptions) {
          $this->logger->warning("UpdateOptions not provided. Using default UpdateOptions.");
          $options = new UpdateOptions();
        }
        $saveResult = $this->update(entityClass: $targetOrEntity::class, partialEntity: $targetOrEntity, conditions: ['id' => $targetOrEntity->id], options: $options);
      } else {
        $saveResult = new SQLQueryResult([], [new NotFoundException($targetOrEntity->id)]);
      }

      if ($saveResult instanceof InsertResult || $saveResult instanceof UpdateResult || $saveResult instanceof DeleteResult) {
        return $saveResult;
      }

      return $this->findBy($targetOrEntity::class, new FindWhereOptions(conditions: ['id' => $this->query->lastInsertId()]));
    }

    foreach ($targetOrEntity as $entity) {
      $results[] = $this->save(targetOrEntity: $entity);
    }

    return new SQLQueryResult($results);
  }

  /**
   * Inserts a given entity into the database.
   * Unlike the save method executes a primitive operation without
   * cascades, relations and other operations included.
   * Executes a fast and efficient `INSERT` query.
   * Does not check if the entity exist in the database, so the query will fail if
   * duplicate entity is being inserted.
   * You can execute bulk inserts using this method.
   *
   * @param string $entityClass The entity class name.
   * @param array|object $entity The entity to insert.
   * @param InsertOptions|null $options The options to use when inserting the entity.
   * @return InsertResult
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   * @throws ReflectionException
   */
  public function insert(string $entityClass, array|object $entity, ?InsertOptions $options = null): InsertResult
  {
    # Check if the entity matches the given entity class
    if (!$this->inspector->hasValidEntityStructure(entity: $entity, entityClass: $entityClass)) {
      return new InsertResult(identifiers: $entity, raw: $this->query->queryString(), generatedMaps: null, errors: [new ORMException("Entity does not match the given entity class.")]);
    }

    $instance = $this->create(entityClass: $entityClass, entityLike: (object)$entity);
    $columnsMeta = [];
    $relations = [];

    if ($options?->relations) {
      $relations = is_object($options->relations) ? (array)$options->relations : $options->relations;
    }

    $columns = $this->inspector->getColumns(entity: $instance, exclude: $this->readonlyColumns, relations: $relations, meta: $columnsMeta);
    $values = $this->inspector->getValues(entity: $instance, exclude: $this->readonlyColumns, options: ['relations' => $relations, 'filter' => true, 'column_types' => $columnsMeta['column_types'] ?? []]);

    $columnCount = count($columns);
    $valueCount = count($values);

    $this->query->insertInto(tableName: $this->inspector->getTableName(entity: $instance))->singleRow(columns: $columns)->values(valuesList: $values);

    if ($this->isDebug) {
      $this->query->debug();
    }

    $result = $this->query->execute();

    if ($result->isError()) {
      if (!headers_sent()) {
        http_response_code(500);
      }
      $error = new GeneralSQLQueryException($this->query);
      Log::error(self::LOG_TAG, $error);

      return new InsertResult(identifiers: $entity, raw: $this->query->queryString(), generatedMaps: $result, errors: [$error]);
    }

    # Find the record by the last insert id and hydrate the entity
    $result = $this->findOne(entityClass: $entityClass, options: new FindOneOptions(where: ['id' => $this->lastInsertId()]));

    if ($result->isError()) {
      if (!headers_sent()) {
        http_response_code(500);
      }
      $error = new GeneralSQLQueryException($this->query);
      Log::error(self::LOG_TAG, $error);

      return new InsertResult(identifiers: $entity, raw: $this->query->queryString(), generatedMaps: $result, errors: [$result->getErrors()]);
    }

    $entity = is_array($result->getData()) ? $result->getData()[0] : $result->getData();

    $generatedMaps = (object)array_merge((array)$entity, (array)new stdClass());
    $generatedMaps->id = $this->lastInsertId();

    foreach ($generatedMaps as $prop => $value) {
      if (in_array($prop, $this->getSecure())) {
        unset($generatedMaps->$prop);
      }
    }

    return new InsertResult(identifiers: $entity, raw: $this->query->queryString(), generatedMaps: $generatedMaps);
  }

  /**
   * Creates a new entity instance or instances. Optionally accepts an object literal with entity
   * properties which will be written into newly created Entity object.
   *
   * @param string $entityClass
   * @param object|array|null $entityLike An entity like object or array literal with entity properties
   *
   * @return object Returns a newly created Entity object
   * @throws ClassNotFoundException
   * @throws ORMException|ReflectionException
   */
  public function create(string $entityClass, null|object|array $entityLike = null): object
  {
    $this->validateEntityName(entityClass: $entityClass);

    $entity = new $entityClass;

    if (!empty($entityLike)) {
      foreach ($entityLike as $entityLikePropertyName => $entityLikePropertyValue) {
        if (property_exists($entity, $entityLikePropertyName)) {
          $entityLikeReflectionProperty = new ReflectionProperty($entityLike, $entityLikePropertyName);
          $entityClassReflectionProperty = new ReflectionProperty($entityClass, $entityLikePropertyName);

          if (is_null($entityLikePropertyValue)) {
            $entityLikePropertyValue = $this->getDefaultColumnValue($entityClassReflectionProperty);

            if (!$entityClassReflectionProperty->getType()->allowsNull()) {
              continue;
            }
          }

          $entityLikeReflectionPropertyType = $entityLikeReflectionProperty->getType()?->getName();
          $entityClassReflectionPropertyType = $entityClassReflectionProperty->getType()?->getName();
          $typesMatch = $entityLikeReflectionPropertyType === $entityClassReflectionPropertyType;

          $sourceType = $entityLikeReflectionPropertyType ?? gettype($entityLikePropertyValue);
          $targetType = $entityClassReflectionPropertyType ?? gettype($entityLikePropertyValue);

          $entity->$entityLikePropertyName = $typesMatch ? $entityLikePropertyValue : $this->castValue(value: $entityLikePropertyValue, sourceType: $sourceType, targetType: $targetType) ?? $entityLikePropertyValue;
        }
      }
    }

    return $entity;
  }

  /**
   * Asserts that the specified class name is a valid entity and throws an exception if it is not.
   *
   * @param string $entityClass The name of the class to validate.
   * @return void
   * @throws ClassNotFoundException If the class does not exist.
   * @throws ORMException If the class does not have the required attributes.
   */
  public static function validateEntityName(string $entityClass): void
  {
    EntityInspector::getInstance()->validateEntityName(entityClass: $entityClass);
  }

  /**
   * Returns the default value for a given column. If no default value is specified, returns null.
   *
   * @param ReflectionProperty $reflectionProperty The reflection property to get the default value for.
   * @return mixed Returns the default value for the given column.
   * @throws ValidationException If the default value is invalid.
   */
  private function getDefaultColumnValue(ReflectionProperty $reflectionProperty): mixed
  {
    # Load Column attribute for the property
    $attributes = $reflectionProperty->getAttributes();

    foreach ($attributes as $attribute) {
      $attributeInstance = $attribute->newInstance();

      if (!property_exists($attributeInstance, 'default')) {
        continue;
      }

      try {
        # Check if a default value is specified
        if (!is_null($attributeInstance->default)) {
          return match (true) {
            $attributeInstance->default === 'CURRENT_TIMESTAMP', $attributeInstance->default === 'NOW()' => new DateTime(),
            # Match ISO 8601 date format
            preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/', $attributeInstance->default) => new DateTime($attributeInstance->default),
            # Match ATOM date format
            preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}.\d+$/', $attributeInstance->default) => new DateTime($attributeInstance->default),
            default => $attributeInstance->default
          };
        }
      } catch (Exception $exception) {
        throw new ValidationException($exception);
      }
    }

    # If no default value is specified, return null
    return null;
  }

  /**
   * @throws ReflectionException
   * @throws TypeConversionException
   */
  private function castValue(mixed $value, string $sourceType, string $targetType): mixed
  {
    if (is_null($value)) {
      return null;
    }

    foreach ($this->customConverters as $converter) {
      $result = $this->typeResolver->resolve(converterHost: $converter, value: $value, fromType: $sourceType, toType: $targetType);

      if (!is_null($result)) {
        return $result;
      }
    }

    foreach ($this->defaultConverters as $converter) {
      $result = $this->typeResolver->resolve(converterHost: $converter, value: $value, fromType: $sourceType, toType: $targetType);

      if (!is_null($result)) {
        return $result;
      }
    }

    return null;
  }

  /**
   * Finds first entity by a given find options.
   * If entity was not found in the database - returns null.
   *
   * @param string $entityClass
   * @param FindOptions|FindOneOptions $options
   * @return FindResult
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   * @throws ReflectionException
   */
  public function findOne(string $entityClass, FindOptions|FindOneOptions $options): FindResult
  {
    $result = $this->find(entityClass: $entityClass, findOptions: $options);

    if ($result->isError()) {
      return $result;
    }

    if ($result->getTotal() === 0) {
      return new FindResult(raw: $result->getRaw(), data: null);
    }

    /** @var Entity $entityClass */
    return new FindResult(raw: $result->getRaw(), data: $result->getData()[0] ?? null);
  }

  /**
   * Find entities that match the given `FindOptions`.
   *
   * @param string $entityClass
   * @param null|FindOptions $findOptions
   *
   * @return FindResult<T>
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   * @throws ReflectionException
   */
  public function find(string $entityClass, ?FindOptions $findOptions = new FindOptions()): FindResult
  {
    $entity = $this->create(entityClass: $entityClass);
    $conditions = [];
    $availableRelations = [];
    $loadedRelations = [];
    $pendingStatements = [];
    $knownAliases = [];

    if ($deleteColumnName = $this->getDeleteDateColumnName(entityClass: $entityClass)) {
      $conditions = array_merge($findOptions->where->conditions ?? $findOptions->where ?? [], [$deleteColumnName => 'NULL']);
    }

    // Get list of relations
    $listOfRelations = $this->getListOfRelations($findOptions);
    $columns = $this->inspector->getColumns(entity: $entity, exclude: $findOptions->exclude ?? [], relations: $listOfRelations, relationProperties: $availableRelations);

    $tableName = $this->inspector->getTableName(entity: $entity);
    $tableAlias = $this->generateAlias($tableName, $knownAliases);

    $statement = $this->query->select()->all(columns: $columns)->from(tableReferences: $tableName);

    if (!empty($findOptions)) {
      # Resolve relations and joins
      if ($findOptions->relations) {
        # $this->buildRelations($listOfRelations);

        foreach ($findOptions->relations as $key => $value) {
          /** @var RelationPropertyMetadata $relationProperty */
          $relationProperty = $availableRelations[$key] ?? $availableRelations[$value] ?? null;

          if (!$relationProperty) {
            if ($_ENV['DEBUG_MODE'] === true) {
              throw new ORMException("Relation $key does not exist in the entity $entityClass.");
            }
            $this->logger->warning("Relation $key does not exist in the entity $entityClass. \n\tThrown in " . __FILE__ . ' on line ' . __LINE__);
            continue;
          }

          if (property_exists($entity, $key)) {
            # TODO: Resolve relations custom options
          }

          if (!$relationProperty->getEntity()) {
            continue;
          }

          switch ($relationProperty->getRelationType()) {
            case RelationType::ONE_TO_ONE:
              # LEFT JOIN
              $joinColumnName = $relationProperty->joinColumn->effectiveColumnName;
              $referencedColumnName = $relationProperty->joinColumn->referencedColumnName ?? 'id';
              $referencedTableName = $relationProperty->getEntity()->table;
              $referencedTableAlias = $this->generateAlias($referencedTableName, $knownAliases);
              $statement = $statement->leftJoin("$referencedTableName $referencedTableAlias")->on("$tableName.$joinColumnName=$referencedTableAlias.$referencedColumnName");
              break;

            case RelationType::ONE_TO_MANY:
              $propertyName = $relationProperty->reflectionProperty->getName();

              $referencedTableName = $tableName;
              $referencedTableAlias = $this->generateAlias($referencedTableName, $knownAliases);
              $referencedPropertyName = $relationProperty->relationAttribute->referencedProperty;
              $referencedColumnName = $propertyName . '_id';

              $foreignClassName = $relationProperty->getEntityClass();
              $foreignClassProperty = $relationProperty->relationAttribute->inverseSide;

              if (!$referencedPropertyName) {
                throw new ORMException("Referenced property name not found for $propertyName");
              }

              if (!$foreignClassName) {
                throw new ORMException("Foreign class name not found for $propertyName");
              }

              if (!$foreignClassProperty) {
                throw new ORMException("Missing inverse side for $foreignClassName");
              }

              $foreignClassTableName = $this->inspector->getTableName(new $foreignClassName());
              $foreignClassTableAlias = $this->generateAlias($foreignClassTableName, $knownAliases);
              # Get the JoinColumn attribute from the inverse side
              $joinColumnAttribute = $this->inspector->getJoinColumnAttribute($foreignClassName, $foreignClassProperty);
              $joinColumnName = $joinColumnAttribute->effectiveColumnName ?? 'id';

              $joinEntity = $this->create($relationProperty->getEntityClass());
              $joinEntityColumns = $this->inspector->getColumns($joinEntity, $findOptions->exclude);

              $joinStatement = new SQLQuery($this->query->getConnection());
              $joinStatement = $joinStatement->select()->all($joinEntityColumns)->from($foreignClassTableName);

              $pendingStatements[] = ['relation' => $relationProperty->name, 'statement' => $joinStatement, 'condition' => "$foreignClassTableName.$joinColumnName=:$referencedPropertyName", 'pattern' => ":$referencedPropertyName", 'replacement' => $referencedPropertyName];
              break;

            case RelationType::MANY_TO_ONE:
              $referencedTableName = $tableName;
              $referencedTableAlias = $this->generateAlias($referencedTableName, $knownAliases);
              $referencedColumnName = $relationProperty->reflectionProperty->getName() . '_id';

              if ($relationProperty->joinColumn?->name) {
                $referencedColumnName = $relationProperty->joinColumn->name;
              }

              $foreignClassName = $relationProperty->getEntityClass();

              if (!$foreignClassName) {
                $this->logger->warning("Foreign class name not found for $referencedColumnName");
                break;
              }

              $foreignClassTableName = $this->inspector->getTableName(new $foreignClassName());
              $foreignClassTableAlias = $this->generateAlias($foreignClassTableName, $knownAliases);
              $joinColumnName = $relationProperty->joinColumn?->referencedColumnName ?? 'id';

              $joinEntity = $this->create($relationProperty->getEntityClass());
              $joinEntityColumns = $this->inspector->getColumns($joinEntity, $findOptions->exclude);
              $cachedStatement = $statement;
              $joinQuery = new SQLQuery($this->query->getConnection());
              $joinStatement = $joinQuery->select()->all($joinEntityColumns)->from([$foreignClassTableName, $referencedTableName])->where("$referencedTableName.$referencedColumnName=$foreignClassTableName.$joinColumnName")->limit(1);

              $joinResult = $joinStatement->execute();
              if ($joinResult->isOK() && !empty($joinResult->getData())) {
                $loadedRelations[$relationProperty->reflectionProperty->getName()] = $joinResult->getData()[0];
              }

              $statement = $cachedStatement;
              break;

            case RelationType::MANY_TO_MANY:
              // TODO Implement many-to-many relation

              # table_1 t1
              $t1Name = $tableName;
              $t1Alias = $t1Name;
              $t1Column = $relationProperty->joinColumn;
              $t1ColumnName = $t1Column?->name ?? 'id';

              # table_2 t2
              $t2Name = $relationProperty->getEntity()->table;
              $t2Alias = $t2Name;
              $t2ColumnName = $t1Column?->referencedColumnName ?? 'id';

              # join_table
              $joinTable = $relationProperty->joinTable;
              $joinTableName = $joinTable?->name;
              $joinTableNameAlias = $joinTableName;
              $joinTableT1ColumnName = $joinTable->joinColumn ?? strtosingular($t1Name) . '_id';
              $joinTableT2ColumnName = $joinTable->inverseJoinColumn ?? strtosingular($t2Name) . '_id';

              $statement = $statement->leftJoin("$joinTableName $joinTableNameAlias")->on("$t1Name.$t1ColumnName = $joinTableNameAlias.$joinTableT1ColumnName")->leftJoin("$t2Name $t2Alias")->on("$joinTableName.$joinTableT2ColumnName = $t2Alias.$t2ColumnName");
              break;
          }
        }
      }

      # Resolve where conditions
      if ($conditions) {
        $findWhereOptions = new FindWhereOptions(conditions: $conditions, entityClass: $entityClass);
      }
      $statement = $statement->where(condition: $findWhereOptions ?? $findOptions);
    }

    $limit = $findOptions->limit ?? $_GET['limit'] ?? Config::get('DEFAULT_LIMIT') ?? 10;
    $skip = $findOptions->skip ?? $_GET['skip'] ?? Config::get('DEFAULT_SKIP') ?? 0;

    $statement = $statement->limit(limit: $limit, offset: $skip);

    if ($this->isDebug) {
      $statement->debug();
    }

    $result = $statement->execute();

    if ($result->isError()) {
      return new FindResult(raw: $result->getRaw(), data: $result->value(), errors: [new GeneralSQLQueryException($this->query), ...$result->getErrors()], affected: $result->getTotalAffectedRows());
    }

    $loadedRelations = [...$loadedRelations, ...$this->processPendingStatements($result, $pendingStatements)];
    $resultSet = $this->processRelations($result->getData(), $entityClass, $findOptions, $availableRelations, $loadedRelations);

    if ($findOptions->withRealTotal) {
      $total = $this->count(entityClass: $entityClass, options: $findOptions);
      return new FindResult(raw: $result->getRaw(), data: $resultSet, errors: $result->getErrors(), total: $total);
    }

    return new FindResult(raw: $result->getRaw(), data: $resultSet, errors: $result->getErrors());
  }

  /**
   * @param string|ReflectionClass $entityClass
   * @return string|null
   * @throws ReflectionException
   */
  private function getDeleteDateColumnName(string|ReflectionClass $entityClass): ?string
  {
    # If $entityClass is a string, get a reflection class
    $reflection = is_string($entityClass) ? new ReflectionClass($entityClass) : $entityClass;

    # Get properties
    $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

    foreach ($properties as $property) {
      # Find DeleteDateColumn attribute
      $deleteDateColumnAttributes = $property->getAttributes(DeleteDateColumn::class);

      if (empty($deleteDateColumnAttributes)) {
        continue;
      }

      # If name is specified use name, else use property name
      $columnInstance = $deleteDateColumnAttributes[0]->newInstance();

      return $columnInstance->name ?? $property->getName();
    }

    return null;
  }

  /**
   * @param FindOptions|null $findOptions
   * @return array|object|null
   */
  protected function getListOfRelations(?FindOptions $findOptions): array|null|object
  {
    return match (gettype($findOptions->relations)) {
      'object' => (array)$findOptions->relations,
      'array' => $findOptions->relations,
      default => []
    };
  }

  /**
   * Generates an alias for a given table name.
   *
   * @param string $tableName The table name to generate an alias for.
   * @param array $knownAliases The list of known aliases.
   * @return string Returns the generated alias.
   */
  private function generateAlias(string $tableName, array &$knownAliases): string
  {
    $tableNameLetters = str_split($tableName);
    $alias = '';

    foreach ($tableNameLetters as $letter) {
      $alias .= $letter;
      if (!in_array($alias, $knownAliases)) {
        $knownAliases[] = $alias;
        return $alias;
      }
    }

    return $alias;
  }

  /**
   * Processes the pending statements.
   *
   * @param SQLQueryResult $result The result of the query.
   * @param array<array{statement: SQLTableReference, condition: string, pattern: string, replacement: string}> $pendingStatements
   * @return array<string, mixed> Returns the loaded relations.
   * @throws ORMException
   */
  private function processPendingStatements(SQLQueryResult $result, array $pendingStatements): array
  {
    $loadedRelations = [];

    if ($result->isError()) {
      return $loadedRelations;
    }

    foreach ($result->getData() as $data) {
      if (is_array($data)) {
        $data = (object)$data;
      }

      foreach ($pendingStatements as $pendingStatement) {
        // Execute the statement
        if (!isset($pendingStatement['replacement'])) {
          $this->logger->error("Cannot find replacement property name");
          continue;
        }

        $referencedPropertyName = $pendingStatement['replacement'];

        if (!isset($data->$referencedPropertyName)) {
          $this->logger->error("Cannot find property $referencedPropertyName in the data");
          continue;
        }

        $replacementValue = $data->$referencedPropertyName;
        $condition = str_replace($pendingStatement['pattern'], $replacementValue, $pendingStatement['condition']);

        // Process the result
        $result = $pendingStatement['statement']->where($condition)->execute();

        // Add the result to the loaded relations
        if ($result->isError()) {
          $error = $result->getErrors()[0] ?? new ORMException("An error occurred while processing the pending statement");
          throw $error;
        }

        $loadedRelations[$pendingStatement['relation']] = $result->getData();
      }
    }

    return $loadedRelations;
  }

  /**
   * Processes the relations of the given data.
   *
   * @param object[] $data The data to process.
   * @param string $entityClass The entity class name.
   * @param FindWhereOptions|FindOptions|null $findOptions The find options.
   * @param RelationPropertyMetadata[] $relationInfo The relation information.
   * @param array $loadedRelations The loaded relations.
   * @return object[] Returns the processed data.
   */
  private function processRelations(array $data, string $entityClass, FindWhereOptions|FindOptions|null $findOptions, array $relationInfo, array $loadedRelations): array
  {
    if (!$findOptions || !$findOptions->relations) {
      return $data;
    }

    $results = [];

    foreach ($data as $datum) {
      foreach ($findOptions->relations as $relation) {
        if (!in_array($relation, array_keys($relationInfo))) {
          $this->logger->warning("Relation $relation does not exist in the entity $entityClass. \n\tThrown in " . __FILE__ . ' on line ' . __LINE__);
          continue;
        }
        $datum = $this->bindEntityRelations($entityClass, $datum, $relation, $relationInfo[$relation], $loadedRelations);
      }

      $results[] = $datum;
    }

    return $results;
  }

  /**
   * Binds entity relations to the entity.
   *
   * @param string $entityClass The entity class name.
   * @param object $entity The entity to bind the relations to.
   * @param string $relationName The name of the relation to bind.
   * @param RelationPropertyMetadata $relationInfo The relation information.
   * @param array<string, mixed> $loadedRelations The loaded relations. This list is used to prevent previously loaded relations from being loaded again.
   * @return object Returns the entity with the relations bound.
   */
  private function bindEntityRelations(string $entityClass, object $entity, string $relationName, RelationPropertyMetadata $relationInfo, array $loadedRelations): object
  {
    $restructuredEntity = new stdClass();
    $relation = new stdClass();
    $relationIsCollection = $relationInfo->type === 'array';

    if ($this->isDebug) {
      $this->logger->debug("Restructuring entity $entityClass with relation $relationName");
    }

    # Foreach relation
    foreach ($entity as $key => $value) {
      $restructuredEntity->$key = $value;

      $tableName = $relationInfo->getEntity()->table;
      $pattern = "/{$tableName}_|$relationName/";

      if (preg_match($pattern, $key)) {
        $relationPropertyName = lcfirst(preg_replace($pattern, '$2', $key));

        if (!$relationPropertyName) {
          $relationPropertyName = $relationInfo->joinColumn->name;
        }

        $relation->$relationPropertyName = $value;
      }
    }

    $relationValue = $loadedRelations[$relationName] ?? $relation;
    $restructuredEntity->$relationName = $relationValue;

    return $restructuredEntity;
  }

  /**
   * Counts entities that match given options.
   * Useful for pagination.
   *
   * @param string $entityClass
   * @param FindOptions|null $options
   * @return int Returns the count of entities that match the given options
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   * @throws ReflectionException
   */
  public function count(string $entityClass, ?FindOptions $options = null): int
  {
    $entity = $this->create(entityClass: $entityClass);

    $statement = $this->query->select()->count()->from(tableReferences: $this->inspector->getTableName(entity: $entity));

    if (!empty($findOptions)) {
      $statement = $statement->where(condition: $options);
    }

    if ($this->isDebug) {
      $statement->debug();
    }

    $result = $statement->execute();

    if ($result->isError()) {
      throw new GeneralSQLQueryException($this->query);
    }

    return $result->value()[0]?->total ?? 0;
  }

  /**
   * Returns the id of the last inserted entity.
   *
   * @return int|null Returns the id of the last inserted entity.
   */
  public function lastInsertId(): ?int
  {
    return $this->query->lastInsertId();
  }

  /**
   * @return array|string[]
   */
  public function getSecure(): array
  {
    return array_merge($this->secure, $this->customSecure);
  }

  /**
   * @param array|string[] $secure
   */
  public function setSecure(array $secure): void
  {
    $this->customSecure = $secure;
  }

  /**
   * Finds entities that match given `FindWhereOptions`.
   *
   * @param string $entityClass The entity class name.
   * @param FindWhereOptions|array $where The find options.
   * @return FindResult Returns a FindResult object representing the result of the query.
   * @throws ClassNotFoundException If the given entity class does not exist.
   * @throws GeneralSQLQueryException If the query fails.
   * @throws ORMException|ReflectionException If the given entity class does not have the required attributes.
   */
  public function findBy(string $entityClass, FindWhereOptions|array $where): FindResult
  {
    $entity = $this->create(entityClass: $entityClass);
    if (is_array($where)) {
      $where = $where['condition'] ?? '';
    }

    $statement = $this->query->select()->all(columns: $this->inspector->getColumns(entity: $entity, exclude: $where->exclude))->from(tableReferences: $this->inspector->getTableName(entity: $entity))->where(condition: $where);

    $limit = $_GET['limit'] ?? 100;
    $skip = $_GET['skip'] ?? 0;

    $statement = $statement->limit(limit: $limit, offset: $skip);

    if ($this->isDebug) {
      $statement->debug();
    }

    $result = $statement->execute();

    if ($result->isError()) {
      throw new GeneralSQLQueryException($this->query);
    }

    return new FindResult(raw: $result->getRaw(), data: $result->getData(), errors: $result->getErrors());
  }

  /**
   * Updates entity partially. Entity can be found by a given condition(s).
   * Unlike save method executes a primitive operation without cascades, relations and other operations included.
   * Executes fast and efficient UPDATE query.
   * Does not check if entity exist in the database.
   * Condition(s) cannot be empty.
   *
   * @param string $entityClass The entity class name.
   * @param object|array $partialEntity The entity or entities to update.
   * @param string|object|array $conditions The condition(s) to find the entity.
   * @param UpdateOptions|null $options The options to use when updating the entity.
   * @return UpdateResult
   * @throws ClassNotFoundException
   * @throws EmptyCriteriaException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   * @throws ReflectionException
   * @throws ValidationException
   */
  public function update(string $entityClass, object|array $partialEntity, string|object|array $conditions, ?UpdateOptions $options = null): UpdateResult
  {
    $this->validateConditions(conditions: $conditions, methodName: __METHOD__);
    $conditionString = '';

    if (empty($conditions)) {
      throw new ORMException("Empty criteria(s) are not allowed for the update method.");
    }

    if (!is_string($conditions)) {
      foreach ($conditions as $key => $value) {
        $conditionString .= "$key=" . match (true) {
            is_numeric($value) => $value,
            $value instanceof UnitEnum && property_exists($value, 'value') => $value->value,
            default => "'$value'"
          };
      }
    } else {
      $conditionString = $conditions;
    }

    if (is_array($partialEntity)) {
      $raw = '';
      $affected = 0;
      $generatedMaps = new stdClass();

      foreach ($partialEntity as $partialItem) {
        $result = $this->update(entityClass: $entityClass, partialEntity: $partialItem, conditions: $conditions);
        $generatedMaps = $result->generatedMaps;
      }

      return new UpdateResult(raw: $this->query->queryString(), affected: $this->query->rowCount(), identifiers: (object)$partialEntity, generatedMaps: $generatedMaps);
    }

    $instance = $this->create(entityClass: $entityClass, entityLike: $partialEntity);
    $assignmentList = [];
    $columnOptions = [];
    $relations = [];

    if ($options?->relations) {
      $relations = is_object($options->relations) ? (array)$options->relations : $options->relations;
    }

    $columnMap = $this->inspector->getColumns(entity: $instance, exclude: $this->readonlyColumns, relations: $relations, meta: $columnOptions);

    foreach ($partialEntity as $prop => $value) {
      # Get the correct prop name
      $columnName = $this->getColumnNameFromProperty($instance, $prop);

      if ($this->mapContainsColumnName($columnMap, $columnName)) {
        if (!is_null($value)) {
          if ($value instanceof UnitEnum && property_exists($value, 'value')) {
            $value = $value->value;
          }

          if ($value instanceof DateTime) {
            $value = $this->inspector->convertDateTimeToString($value, $prop, $columnOptions);
          }

          if ($value instanceof stdClass) {
            $value = json_encode($value);
          }
          $assignmentList[$columnName] = $value;
        }
      }
    }

    $this->query->update(tableName: $this->inspector->getTableName(entity: $instance))->set(assignmentList: $assignmentList)->where(condition: $conditionString);

    if ($this->isDebug) {
      $this->query->debug();
    }

    $result = $this->query->execute();

    if ($result->isError()) {
      throw new GeneralSQLQueryException($this->query);
    }

    $updatedEntity = $this->findOne(entityClass: $entityClass, options: new FindOptions(where: $conditions));
    $generatedMaps = $updatedEntity->getData() ?? new stdClass();

    return new UpdateResult(raw: $this->query->queryString(), affected: $this->query->rowCount(), identifiers: $partialEntity, generatedMaps: $generatedMaps);
  }

  /**
   * Validates the given conditions. If the given conditions are invalid, then a `ValidationException` is thrown.
   *
   * @param string|object|array $conditions The conditions to validate.
   * @param string $methodName The name of the method that called this method.
   * @return void
   * @throws ValidationException If the given conditions are invalid.
   */
  private function validateConditions(string|object|array $conditions, string $methodName): void
  {
    if (empty($conditions)) {
      throw new EmptyCriteriaException(methodName: $methodName);
    }
  }

  /**
   * Returns the column name for a given property. If no column name is specified, returns the property name.
   *
   * @param object $entity The entity to get the column name for.
   * @param string $prop The property to get the column name for.
   * @return string Returns the column name for the given property.
   * @throws ReflectionException If the property does not exist.
   */
  private function getColumnNameFromProperty(object $entity, string $prop): string
  {
    if (!property_exists($entity, $prop)) {
      # Check if this is a relation property

      # Get all entities with
      return $prop;
    }
    $propertyReflection = new ReflectionProperty($entity, $prop);
    $attributes = $propertyReflection->getAttributes();

    if (empty($attributes)) {
      return $prop;
    }

    # Return $prop if no valid Column attribute found
    $columnClassNames = [Column::class, CreateDateColumn::class, DeleteDateColumn::class, EmailColumn::class, PasswordColumn::class, PrimaryGeneratedColumn::class, UpdateDateColumn::class, URLColumn::class];
    $columnAttribute = null;
    foreach ($attributes as $attribute) {
      if (in_array($attribute->getName(), $columnClassNames)) {
        $columnAttribute = $attribute;
      }
    }

    if (!$columnAttribute) {
      return $prop;
    }

    /** @var Column $column */
    $column = $columnAttribute->newInstance();
    return empty($column->name) ? $prop : $column->name;
  }

  /**
   * Determines if a given column map contains a given column name.
   *
   * @param array $columnMap The column map to check.
   * @param string $columnName The column name to check for.
   * @return bool Returns true if the column map contains the given column name, otherwise false.
   */
  private function mapContainsColumnName(array $columnMap, string $columnName): bool
  {
    foreach ($columnMap as $key => $value) {
      if (str_ends_with($key, $columnName)) {
        return true;
      }

      if (str_ends_with($value, $columnName)) {
        return true;
      }
    }
    return false;
  }

  /**
   * Creates a new entity from the given plain php object. If the entity already exist in the database, then
   * it loads it (and everything related to it), replaces all values with the new ones from the given object
   * and returns this new entity. This new entity is actually a loaded from the db entity with all properties
   * replaced from the new object.
   * @param string $entityClass
   * @param object $entityLike
   * @return Entity|null
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   * @throws ReflectionException
   */
  public function preload(string $entityClass, object $entityLike): ?object
  {
    $findOptions = [];

    foreach ($entityLike as $prop => $value) {
      if (property_exists($entityClass, $prop)) {
        $findOptions[$prop] = $value;
      }
    }

    $entity = $this->find(entityClass: $entityClass, findOptions: FindOptions::fromArray($findOptions));

    if (empty($entity->getData())) {
      $entity = $this->create(entityClass: $entityClass, entityLike: $entityLike);
    }

    return $this->merge($entityClass, $entity, $entityLike);
  }

  /**
   * Merges multiple entities into a single entity.
   *
   * @param string $entityClass
   * @param mixed ...$entities
   *
   * @return Entity Returns a single entity
   * @throws ClassNotFoundException
   * @throws ORMException
   */
  public function merge(string $entityClass, ...$entities): object
  {
    $this->validateEntityName(entityClass: $entityClass);

    $entity = new $entityClass;

    if ($entity instanceof Entity) {
      $object = $entity;

      foreach ($entities as $item) {
        if (is_object($item) || is_array($item)) {
          $object = (object)array_merge((array)$object, (array)$item);
        }
      }

      foreach ($object as $prop => $value) {
        if (property_exists($entity, $prop)) {
          $entity->$prop = $value;
        }
      }
    }

    return $entity;
  }

  /**
   * Inserts a new entity or array of entities unless they already exist in the database, if they do then updates.
   *
   * @param string $entityClass The entity class name.
   * @param object|object[] $entityOrEntities The entity or entities to upsert.
   * @return InsertResult|UpdateResult Returns an InsertResult or UpdateResult.
   * @throws ClassNotFoundException
   * @throws NotImplementedException
   * @throws ORMException
   * @throws ReflectionException
   */
  public function upsert(string $entityClass, object|array $entityOrEntities, array|UpsertOptions $options): InsertResult|UpdateResult
  {
    // TODO: #83 Implement EntityManager::upsert @amasiye
    if (is_array($entityOrEntities)) {
      $results = [];
      $errors = [];
      foreach ($entityOrEntities as $entity) {
        $result = $this->upsert(entityClass: $entityClass, entityOrEntities: $entity, options: $options);

        if ($result->isError()) {
          $errors[] = $result->getErrors();
        }

        $results[] = $result->getData();
      }

      $generatedMaps = new stdClass();
      $generatedMaps->results = $results;

      return new UpdateResult(raw: $this->query->queryString(), affected: $this->query->rowCount(), identifiers: $entityOrEntities, generatedMaps: $generatedMaps, errors: $errors);
    }

    $this->validateEntityName(entityClass: $entityClass);

    // TODO: Configure the upsert options

    $columns = $this->inspector->getColumns(entity: $entityOrEntities);
    $updateColumns = $this->inspector->getColumns(entity: $entityOrEntities, exclude: $this->readonlyColumns);
    $values = $this->inspector->getValues(entity: $entityOrEntities);

    $assignmentList = array_map(fn($column) => "$column=VALUES($column)", $updateColumns);

    $this->query->insertInto(tableName: $this->inspector->getTableName(entity: $entityOrEntities))->singleRow(columns: $columns)->values(valuesList: $values)->onDuplicateKeyUpdate(assignmentList: $assignmentList);

    if ($this->isDebug) {
      $this->query->debug();
    }

    $result = $this->query->execute();

    $errors = [];
    if ($result->isError()) {
      $errors[] = $this->query->getConnection()->errorInfo();
      $errors[] = new GeneralSQLQueryException($this->query);
    }

    $generatedMaps = $entityOrEntities;

    if ($result->isOk()) {
      $generatedMaps->id = $this->lastInsertId();
    }

    return new InsertResult(identifiers: (object)['id' => $this->lastInsertId()], raw: $this->query->queryString(), generatedMaps: $entityOrEntities, errors: $errors);
  }

  /**
   * Removes a given entity from the database.
   *
   * @param object|object[] $entityOrEntities
   * @param RemoveOptions|null $removeOptions
   * @return DeleteResult
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   */
  public function remove(object|array $entityOrEntities, ?RemoveOptions $removeOptions = null): DeleteResult
  {
    if (is_object($entityOrEntities)) {
      $id = $entityOrEntities->id ?? 0;
      $statement = $this->query->deleteFrom(tableName: $this->inspector->getTableName(entity: $entityOrEntities))->where("id=$id");

      if ($this->isDebug) {
        $statement->debug();
      }

      $result = $statement->execute();

      if ($result->isError()) {
        throw new GeneralSQLQueryException($this->query);
      }

      return new DeleteResult(raw: $this->query->queryString(), affected: $result->getTotalAffectedRows());
    }

    $affected = 0;
    $raw = '';
    foreach ($entityOrEntities as $entity) {
      $removeResult = $this->remove(entityOrEntities: $entity, removeOptions: $removeOptions);
      $affected += $removeResult->affected;
      $raw .= $removeResult->raw . PHP_EOL;
    }

    return new DeleteResult(raw: $raw, affected: $affected);
  }

  /**
   * Records the deletion date of a given entity.
   *
   * @param object|object[] $entityOrEntities
   * @param RemoveOptions|array|null $removeOptions
   * @return UpdateResult Returns the removed entities.
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   */
  public function softRemove(object|array $entityOrEntities, RemoveOptions|array|null $removeOptions = null): UpdateResult
  {
    $result = null;
    $deletedAt = date(DATE_ATOM);

    if (is_object($entityOrEntities)) {
      if (!$entityOrEntities->id) {
        throw new ORMException("Entity must have an id to be soft removed.");
      }

      $statement = $this->query->update(tableName: $this->inspector->getTableName(entity: $entityOrEntities))->set([Filter::getDeleteDateColumnName(entity: $entityOrEntities) => $deletedAt])->where("id=$entityOrEntities->id");

      if ($this->isDebug) {
        $statement->debug();
      }

      $result = $statement->execute();

      if ($result->isError()) {
        throw new GeneralSQLQueryException($this->query);
      }

      // TODO: #88 Verify that delete occurred @amasiye
      $generatedMaps = new stdClass();
      foreach ($result->value() as $key => $value) {
        $generatedMaps->$key = $value;
      }

      return new UpdateResult(raw: $this->query->queryString(), affected: $this->query->rowCount(), identifiers: (object)$entityOrEntities, generatedMaps: $generatedMaps);
    }

    $identifiers = new stdClass();
    $generatedMaps = new stdClass();

    $numberFormatter = new NumberFormatter('en', NumberFormatter::SPELLOUT);
    foreach ($entityOrEntities as $id => $entity) {
      $key = is_numeric($id) ? $numberFormatter->format($id) : $id;
      $result = $this->softRemove(entityOrEntities: $entity, removeOptions: $removeOptions);
      $identifiers->$key = $result;
      $generatedMaps->$key = $result->generatedMaps;
    }

    return new UpdateResult(raw: $result, affected: $this->query->rowCount(), identifiers: $identifiers, generatedMaps: $generatedMaps);
  }

  /**
   * Deletes entities by a given condition(s).
   *
   * Unlike the save method, it executes a primitive operation without cascades,
   * relations and other operations included.
   * Executes a fast and efficient `DELETE` query.
   * Does not check if the entity exists in the database.
   * Condition(s) cannot be empty.
   *
   * @param string $entityClass
   * @param int|array|object $conditions The deletion conditions.
   *
   * @return DeleteResult Returns the removed entities.
   * @throws ClassNotFoundException
   * @throws EmptyCriteriaException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   * @throws ReflectionException
   */
  public function delete(string $entityClass, int|array|object $conditions): DeleteResult
  {
    $this->validateConditions(conditions: $conditions, methodName: __METHOD__);

    $entity = $this->create(entityClass: $entityClass);

    $statement = $this->query->deleteFrom(tableName: $this->inspector->getTableName(entity: $entity))->where(condition: $this->getConditionsString(conditions: $conditions));

    if ($this->isDebug) {
      $statement->debug();
    }

    $deletionResult = $statement->execute();

    if ($deletionResult->isError()) {
      throw new GeneralSQLQueryException($this->query);
    }

    return new DeleteResult(raw: $deletionResult->value(), affected: $this->query->rowCount());
  }

  /**
   * @param int|object|array $conditions
   *
   * @return string Returns an SQL condition string
   */
  private function getConditionsString(int|object|array $conditions): string
  {
    $separator = ', ';
    $conditionsString = '';

    if (empty($conditions)) {
      return '';
    }

    if (is_int($conditions)) {
      $conditionsString = sprintf("id=%s", $conditions);
    } else {
      foreach ($conditions as $key => $value) {
        $conditionsString .= sprintf("%s=%s%s", $key, (is_numeric($value) ? $value : "'$value'"), $separator);
      }
    }

    return trim($conditionsString, $separator);
  }

  /**
   * Restores entities by a given condition(s).
   * Unlike save method executes a primitive operation without cascades, relations and other operations included.
   * Executes fast and efficient DELETE query.
   * Does not check if entity exist in the database.
   * Condition(s) cannot be empty.
   * @param string $entityClass
   * @param int|array|object $conditions
   * @return UpdateResult
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   * @throws ReflectionException
   */
  public function restore(string $entityClass, int|array|object $conditions): UpdateResult
  {
    $entity = $this->create(entityClass: $entityClass);

    $statement = $this->query->update(tableName: $this->inspector->getTableName(entity: $entity))->set([Filter::getDeleteDateColumnName(entity: $entity) => NULL])->where(condition: $this->getConditionsString(conditions: $conditions));

    if ($this->isDebug) {
      $statement->debug();
    }

    $restoreResult = $statement->execute();

    if ($restoreResult->isError()) {
      throw new GeneralSQLQueryException($this->query);
    }

    $generatedMaps = new stdClass();

    foreach ($restoreResult->value() as $key => $value) {
      $generatedMaps->$key = $value;
    }

    return new UpdateResult(raw: $restoreResult->value(), affected: $this->query->rowCount(), identifiers: $entity, generatedMaps: $generatedMaps);
  }

  /**
   * Finds entities that match given find options.
   * Also counts all entities that match given conditions,
   * but ignores pagination settings (from and take options).
   *
   * @param string $entityClass
   * @param FindManyOptions|null $options
   * @return FindResult
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   * @throws ReflectionException
   */
  #[ArrayShape(['entities' => "array|null", 'count' => "int"])]
  public function findAndCount(string $entityClass, ?FindManyOptions $options = null): FindResult
  {
    $result = $this->find(entityClass: $entityClass, findOptions: $options);

    return new FindResult($result->getRaw(), $result->getTotal(), $result->getErrors(), $result->getTotalAffectedRows());
  }

  /**
   * Finds entities that match given WHERE conditions.
   * Also counts all entities that match given conditions,
   * but ignores pagination settings (from and take options).
   * @param string $entityClass
   * @param FindWhereOptions|array $where
   * @return FindResult
   * @throws ClassNotFoundException
   * @throws GeneralSQLQueryException
   * @throws ORMException
   * @throws ReflectionException
   */
  public function findAndCountBy(string $entityClass, FindWhereOptions|array $where): FindResult
  {
    $result = $this->findBy(entityClass: $entityClass, where: $where);

    return new FindResult($result->getRaw(), $result->getTotal(), $result->getErrors(), $result->getTotalAffectedRows());
  }

  /**
   * Sets the list of custom converters to use for type conversion.
   *
   * @param array $converters
   * @return void
   */
  public function useConverters(array $converters): void
  {
    $this->customConverters = $converters;
  }

  /**
   * @param string $entityClassName
   * @param object $object
   * @param IFactory|null $factory
   * @return object
   * @throws ClassNotFoundException
   * @throws ORMException
   * @throws ReflectionException
   * @throws ContainerException
   */
  public function getEntityFromObject(string $entityClassName, object $object, ?IFactory $factory = null): object
  {
    self::validateEntityName($entityClassName);

    $provider = new EntityProvider($this, $factory);
    $entity = $provider->get($entityClassName);

    foreach ($object as $prop => $value) {
      if (property_exists($entity, $prop)) {
        $sourceReflection = new ReflectionProperty($object, $prop);
        $targetReflection = new ReflectionProperty($entity, $prop);

        $sourceType = $sourceReflection->getType()?->getName() ?? match (gettype($object->$prop)) {
          'integer' => 'int',
          'double' => 'float',
          'NULL' => null,
          'object' => get_class($object->$prop),
          default => gettype($object->$prop)
        };
        $targetType = $targetReflection->getType()?->getName() ?? match (gettype($entity->$prop)) {
          'integer' => 'int',
          'double' => 'float',
          'NULL' => null,
          'object' => get_class($object->$prop),
          default => gettype($entity->$prop)
        };

        if (is_null($sourceType) || is_null($targetType)) {
          continue;
        }

        if ($sourceType !== $targetType) {
          $value = $this->castValue(value: $value, sourceType: $sourceType, targetType: $targetType);
        }

        $entity->$prop = $value;
      }
    }

    return $entity;
  }

  /**
   * @param string|null $name
   * @return array
   */
  public function getStore(?string $name = null): array
  {
    return $this->entities;
  }

  /**
   * @param string $key
   * @return object|null
   */
  public function getStoreEntry(string $key): ?object
  {
    return $this->hasStoreEntry($key) ? $this->getStoreEntry($key) : null;
  }

  /**
   * @param string $key
   * @return bool
   */
  public function hasStoreEntry(string $key): bool
  {
    return isset($this->entities[$key]);
  }

  /**
   * @param string $key
   * @param object $value
   * @return int
   */
  public function addStoreEntry(string $key, object $value): int
  {
    return count($this->entities);
  }

  /**
   * @param string $key
   * @param object $value
   * @return int
   */
  public function removeStoreEntry(string $key, object $value): int
  {
    return count($this->entities);
  }
}